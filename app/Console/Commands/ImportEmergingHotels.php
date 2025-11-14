<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Импорт большого JSONL (в т.ч. .zst) из Emerging.
 *
 * Примеры:
 *  php artisan emerging:import-jsonl https://partner-feedora.s3.../partner_feed_en_v3.jsonl.zst --limit=20000
 *  php artisan emerging:import-jsonl storage/app/partner_feed_en_v3.jsonl.zst --offset=40000 --limit=20000
 *  curl -L 'https://.../file.jsonl.zst' | php artisan emerging:import-jsonl - --offset=0 --limit=5000
 */
class ImportEmergingHotels extends Command
{
    protected $signature = 'emerging:import-hotels
        {source : URL, путь к .zst/.jsonl или "-" для STDIN}
        {--offset=0 : Номер строки (0-based) с которой начинать}
        {--limit=0 : Сколько строк обработать (0 — без лимита)}
        {--save-progress=1 : Сохранять прогресс в файл}
        {--progress-file=storage/app/emerging_offset.json : Путь к файлу прогресса}
        {--max-retries=7 : Кол-во попыток докачки}
    ';

    protected $description = 'Импорт JSONL (в т.ч. .zst) из Emerging с резюмом, оффсетом и лимитом';

    private function downloadWithResume(string $url, string $destPath): void
    {
        // 1) HEAD: длина и ETag
        $head = Http::retry(3, 500)->head($url);
        if ($head->failed()) {
            throw new RequestException($head);
        }

        $remoteLen = (int) ($head->header('Content-Length') ?? 0);
        $etag      = trim((string) $head->header('ETag'), '"');
        if ($remoteLen <= 0) {
            throw new \RuntimeException('Не удалось получить Content-Length от сервера.');
        }

        // 2) Локальные данные
        $metaPath   = $destPath . '.meta.json';
        $dir        = dirname($destPath);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new \RuntimeException("Не удалось создать каталог: {$dir}");
            }
        }

        $hasFile    = file_exists($destPath);
        $localSize  = $hasFile ? (int) filesize($destPath) : 0;
        $savedMeta  = is_file($metaPath) ? json_decode((string) file_get_contents($metaPath), true) : null;
        $sameObject = $savedMeta && ($savedMeta['etag'] ?? null) === $etag && (int) ($savedMeta['len'] ?? 0) === $remoteLen;

        // Если объект изменился на S3 — сбрасываем локальный файл
        if ($hasFile && !$sameObject) {
            @unlink($destPath);
            $localSize = 0;
        }

        // 3) Уже всё скачано?
        if ($localSize >= $remoteLen) {
            if ($localSize > $remoteLen) {
                $fp = fopen($destPath, 'c+');
                if ($fp) {
                    ftruncate($fp, $remoteLen);
                    fclose($fp);
                }
            }
            file_put_contents($metaPath, json_encode(['etag' => $etag, 'len' => $remoteLen]));
            return;
        }

        // 4) Заголовки для докачки
        $headers = [
            // Иногда S3 более предсказуем с явным User-Agent
            'User-Agent' => 'Staybook-Importer/1.0',
            'Accept'     => 'application/octet-stream',
        ];
        if ($localSize > 0) {
            $headers['Range'] = "bytes={$localSize}-";
        }

        // 5) Качаем напрямую в файл
        $beforeSize = file_exists($destPath) ? (int) filesize($destPath) : 0;
        $response   = $this->streamToFileWithSink($url, $destPath, $localSize, $headers);

        // 6) 416 (Range Not Satisfiable)
        if ($response->status() === 416) {
            clearstatcache(true, $destPath);
            $actual = file_exists($destPath) ? (int) filesize($destPath) : 0;

            if ($actual === $remoteLen) {
                file_put_contents($metaPath, json_encode(['etag' => $etag, 'len' => $remoteLen]));
                return;
            }

            @unlink($destPath);
            $localSize = 0;

            // Перекачка без Range
            $response = $this->streamToFileWithSink($url, $destPath, 0, array_diff_key($headers, ['Range'=>true]));
        }

        // 7) Проверка статуса
        if (!in_array($response->status(), [200, 206], true)) {
            throw new RequestException($response);
        }

        // 8) Проверка, что файл реально вырос
        clearstatcache(true, $destPath);
        $afterSize = file_exists($destPath) ? (int) filesize($destPath) : 0;
        if ($afterSize <= $beforeSize) {
            // Диагноз: сервер вернул тело 0 байт либо запись не происходила.
            // Дадим более явное сообщение
            $dbg = json_encode([
                'status' => $response->status(),
                'range'  => $headers['Range'] ?? null,
                'head_len' => $remoteLen,
                'before' => $beforeSize,
                'after'  => $afterSize,
            ], JSON_UNESCAPED_SLASHES);
            throw new \RuntimeException("Ответ не был записан в файл (нет прироста размера). Отладка: {$dbg}");
        }

        // 9) Финальная валидация размера (после полной загрузки)
        if ($afterSize !== $remoteLen) {
            // Если мы делали частичную докачку, возможно ещё не весь файл — повторим цикл,
            // но в этой реализации мы ожидаем полное совпадение после одного запроса.
            // Чтобы поддержать многошаговую докачку, можно рекурсивно вызвать downloadWithResume().
            // Для простоты бросаем исключение:
            throw new \RuntimeException("Размер не совпал после скачивания ({$afterSize} != {$remoteLen}).");
        }

        // 10) Сохраняем метаданные
        file_put_contents($metaPath, json_encode(['etag' => $etag, 'len' => $remoteLen]));
    }

    /**
     * Качает ответ напрямую в файл с помощью опции Guzzle 'sink'.
     * ВАЖНО:
     *  - НЕ используем 'stream' => true (с sink не нужно).
     *  - Гарантируем позицию курсора (fseek) при докачке.
     *  - Закрываем дескриптор после завершения запроса.
     */
    private function streamToFileWithSink(
        string $url,
        string $destPath,
        int $localSize = 0,
        array $headers = [],
        array $options = []
    ): \Illuminate\Http\Client\Response {
        // Открыть файл и поставить курсор
        $fp = fopen($destPath, 'c+');
        if ($fp === false) {
            throw new \RuntimeException("Не могу открыть файл: {$destPath}");
        }
        if ($localSize > 0) {
            // Ставим указатель в конец уже скачанной части
            if (fseek($fp, $localSize) !== 0) {
                fclose($fp);
                throw new \RuntimeException("Не удалось выполнить fseek({$localSize}) для {$destPath}");
            }
        }

        try {
            $response = Http::withHeaders($headers)
                ->withOptions(array_merge([
                    'sink'            => $fp,    // писать прямо в файл
                    'http_errors'     => false,  // статусы проверяем сами
                    'allow_redirects' => true,   // на всякий случай
                    'read_timeout'    => 300,    // можно увеличить
                    'connect_timeout' => 30,
                ], $options))
                ->timeout(0)                   // без общего таймаута
                ->retry(3, 1000)
                ->get($url);

            // На macOS/некоторых FS полезно принудительно сбросить буферы
            fflush($fp);

            return $response;
        } finally {
            if (is_resource($fp)) {
                fclose($fp);
            }
        }
    }


    /** итерация по строкам JSONL из .zst через zstd -dc */
    protected function iterateJsonlFromZstd(string $zstPath, callable $onLine): void
    {
        $cmd = sprintf('zstd -dc %s', escapeshellarg($zstPath));
        $desc = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = proc_open($cmd, $desc, $pipes, null, null);
        if (!\is_resource($proc)) {
            throw new \RuntimeException('Cannot start zstd process');
        }
        fclose($pipes[0]);

        $out = $pipes[1];
        stream_set_timeout($out, 60);

        $lineNo = 0;
        while (!feof($out)) {
            $line = fgets($out);
            if ($line === false) {
                $info = stream_get_meta_data($out);
                if (($info['timed_out'] ?? false) === true) {
                    continue;
                }
                break;
            }
            $lineNo++;
            $trim = trim($line);
            if ($trim === '') continue;

            $row = json_decode($trim, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning("Skip invalid JSON at #{$lineNo}");
                continue;
            }
            $onLine($row, $lineNo);
        }

        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        proc_close($proc);

        if ($stderr) {
            Log::debug('zstd stderr: ' . $stderr);
        }
    }

    /** итерация по строкам plain JSONL (файл или STDIN) */
    protected function iterateJsonlPlain($stream, callable $onLine): void
    {
        $lineNo = 0;
        while (!feof($stream)) {
            $line = fgets($stream);
            if ($line === false) break;
            $lineNo++;
            $trim = trim($line);
            if ($trim === '') continue;

            $row = json_decode($trim, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning("Skip invalid JSON at #{$lineNo}");
                continue;
            }
            $onLine($row, $lineNo);
        }
    }

    /** пример сохранения строки в БД (адаптируй под свои модели) */
    protected function upsertHotel(array $row): void
    {
        // минимальный фильтр: нужен hid и kind=Hotel|hotel
        $kind = strtolower((string)($row['kind'] ?? ''));
        $hid  = $row['hid'] ?? null;
        if (!$hid || !in_array($kind, ['hotel', 'апартаменты','hostel','guest house','motel','inn','resort','ryokan','ryokan (japan)'])) {
            return;
        }

        $amenitiesHotel = collect($row['amenity_groups'] ?? [])
            ->firstWhere('group_name', 'Services and amenities')['amenities'] ?? [];
        $amenitiesHotel = implode(', ', (array)$amenitiesHotel);

        $region = (array)($row['region'] ?? []);
        $city   = (string)($region['name'] ?? '');
        $cc     = (string)($region['country_code'] ?? '');

        // utc по стране — по желанию: EmergingTools::getUtcOffsetByCountryCode($cc)
        $utc = '';

        /** @var \App\Models\Hotel $Hotel */
        \App\Models\Hotel::updateOrCreate(
            ['emerging_id' => $hid],
            [
                'code'         => (string)($row['id'] ?? ''),
                'title'        => (string)($row['name'] ?? ''),
                'title_en'     => (string)($row['name'] ?? ''),
                'type'         => (string)($row['kind'] ?? ''),
                'rating'       => (int)($row['star_rating'] ?? 0),
                'address_en'   => (string)($row['address'] ?? ''),
                'city'         => $city,
                'country_code' => $cc,
                'utc'          => $utc,
                'lat'          => (float)($row['latitude'] ?? 0),
                'lng'          => (float)($row['longitude'] ?? 0),
                'checkin'      => (string)($row['check_in_time'] ?? ''),
                'checkout'     => (string)($row['check_out_time'] ?? ''),
                'phone'        => (string)($row['phone'] ?? ''),
                'email'        => (string)($row['email'] ?? ''),
                'description_en'=> '',
                'image'        => '',
                'status'       => 1,
                'user_id'      => 1,
            ]
        );

        // по желанию: \App\Models\Amenity::updateOrCreate([...], [...])
        // и т.п. — чтобы не тянуть сюда лишнего, оставил только Hotel.
    }

    public function handle(): int
    {
        ignore_user_abort(true);
        set_time_limit(0);

        $source      = (string)$this->argument('source');
        $offsetArg   = (int)$this->option('offset');
        $limit       = (int)$this->option('limit');
        $saveProg    = (bool)$this->option('save-progress');
        $progressFn  = (string)$this->option('progress-file');
        $maxRetries  = (int)$this->option('max-retries');

        // прогресс из файла (если надо)
        $offset = $offsetArg;
        if ($saveProg && $offset === 0 && is_file(base_path($progressFn))) {
            $json = @file_get_contents(base_path($progressFn));
            if ($json) {
                $st = json_decode($json, true);
                if (isset($st['offset'])) {
                    $offset = (int)$st['offset'];
                    $this->warn("Resume from saved offset: $offset");
                }
            }
        }

        // определяем тип источника
        $isStdIn = ($source === '-');
        $isUrl   = (stripos($source, 'http://') === 0 || stripos($source, 'https://') === 0);

        // если URL — докачиваем в локальный .zst
        $localPath = $source;
        if ($isUrl) {
            $localPath = storage_path('app/partner_feed_en_v3.jsonl.zst');
            $this->downloadWithResume($source, $localPath, $maxRetries);
        }

        $this->info("Start at line {$offset}, limit " . ($limit ?: '∞') . "…");

        $processed = 0;
        $seen = 0;
        $startTs = microtime(true);

        $onLine = function (array $row, int $lineNo) use (&$offset, $limit, &$processed, &$seen, $saveProg, $progressFn) {
            $seen++;

            if ($lineNo <= $offset) {
                return;
            }
            if ($limit > 0 && $processed >= $limit) {
                return;
            }

            // бизнес-логика: сохраняем строку
            try {
                $this->upsertHotel($row);
            } catch (\Throwable $e) {
                Log::error('Import line failed', ['line' => $lineNo, 'e' => $e->getMessage()]);
            }

            $processed++;
            $offset = $lineNo;

            // периодический прогресс
            if (($processed % 500) === 0) {
                $this->output->writeln(
                    "Processed={$processed}, lastLine={$lineNo} (mem: " . number_format(memory_get_usage(true)) . ")"
                );
                if ($saveProg) {
                    @file_put_contents(base_path($progressFn), json_encode(['offset' => $offset]));
                }
                // сборщик мусора на больших объёмах
                if (function_exists('gc_collect_cycles')) gc_collect_cycles();
            }
        };

        // читаем источник
        if ($isStdIn) {
            $this->line('Reading from STDIN…');
            $this->iterateJsonlPlain(STDIN, $onLine);
        } else {
            // определим расширение
            $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
            if (!is_file($localPath)) {
                $this->error("Source not found: $localPath");
                return self::FAILURE;
            }
            if ($ext === 'zst') {
                $this->iterateJsonlFromZstd($localPath, $onLine);
            } else {
                $fp = fopen($localPath, 'rb');
                if (!$fp) {
                    $this->error("Cannot open $localPath");
                    return self::FAILURE;
                }
                $this->iterateJsonlPlain($fp, $onLine);
                fclose($fp);
            }
        }

        // финальный прогресс
        if ($saveProg) {
            @file_put_contents(base_path($progressFn), json_encode(['offset' => $offset]));
        }

        $elapsed = round(microtime(true) - $startTs, 1);
        $this->info("Done. Processed: {$processed}. Next offset: {$offset}. Time: {$elapsed}s");

        if ($limit > 0 && $processed < $limit) {
            $this->warn("Достигнут конец данных раньше лимита: processed={$processed}, limit={$limit}");
        } else {
            $this->line("Next run: php artisan emerging:import-jsonl '{$this->argument('source')}' --offset={$offset} --limit={$limit}");
        }

        return self::SUCCESS;
    }
}