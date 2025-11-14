<?php

namespace App\Http\Controllers\API\V1\Emerging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\City;

class EmergingRegionController extends Controller
{
    public function fetchRegionStatic(Request $request)
    {
        // Всегда режим "только города"
        [$path, $kind, $sourceUsed] = $this->materializeSourceToFile($request);
        if (!$path || !is_file($path)) {
            Log::warning('[CitiesImport] No source file', ['source' => $sourceUsed]);
            return response()->json(['error' => 'Source not found', 'source' => $sourceUsed], 422);
        }

        $chunkSize = (int) env('CITY_UPSERT_CHUNK', 1000);
        $received = 0;
        $affected = 0;

        if ($kind === 'jsonl') {
            [$received, $affected] = $this->processJsonlStream($path, $chunkSize);
        } else {
            [$received, $affected] = $this->processJsonArrayStream($path, $chunkSize);
        }

        return response()->json([
            'mode'     => 'only_cities',
            'source'   => $sourceUsed,
            'format'   => $kind,
            'received' => $received,
            'affected' => $affected,
        ]);
    }

    /* ---------------- loaders ---------------- */

    protected function materializeSourceToFile(Request $request): array
    {
        $src = $request->query('source') ?: env('REGION_FEED_URL');
        if (!$src) {
            $defaultJson  = storage_path('app/region_static.json');
            $defaultJsonl = storage_path('app/region_static.jsonl');
            if (is_file($defaultJsonl)) return [$defaultJsonl, 'jsonl', $defaultJsonl];
            if (is_file($defaultJson))  return [$defaultJson,  'json',  $defaultJson];
            return [null, null, 'empty'];
        }

        if (is_file($src)) {
            $ext  = strtolower(pathinfo($src, PATHINFO_EXTENSION));
            $kind = ($ext === 'jsonl') ? 'jsonl' : 'json';
            return [$src, $kind, $src];
        }

        if (preg_match('~^https?://~i', $src)) {
            $ext  = strtolower(pathinfo(parse_url($src, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) ?: 'json';
            $kind = ($ext === 'jsonl') ? 'jsonl' : 'json';
            $tmp  = storage_path('app/tmp/cities_'.uniqid().'.'.$ext);
            @mkdir(dirname($tmp), 0777, true);
            try {
                \Http::timeout(0)->sink($tmp)->get($src);
                if (!is_file($tmp) || filesize($tmp) === 0) {
                    Log::error('[CitiesImport] Download empty', ['url' => $src]);
                    return [null, null, $src];
                }
                return [$tmp, $kind, $src];
            } catch (\Throwable $e) {
                Log::error('[CitiesImport] Download failed', ['url' => $src, 'e' => $e->getMessage()]);
                return [null, null, $src];
            }
        }

        return [null, null, $src];
    }

    /* ---------------- stream processors ---------------- */

    protected function processJsonlStream(string $path, int $chunkSize): array
    {
        $file = new \SplFileObject($path, 'r');
        $file->setFlags(\SplFileObject::READ_AHEAD | \SplFileObject::DROP_NEW_LINE);

        $now = now();
        $buf = [];
        $received = 0;
        $affected = 0;

        DB::disableQueryLog();

        while (!$file->eof()) {
            $line = $file->fgets();
            if ($line === false) break;
            $line = trim($line);
            if ($line === '') continue;

            $row = json_decode($line, true);
            if (!is_array($row)) continue;

            // ожидаем, что JSONL — это сразу города
            if ($mapped = $this->mapCityRowFlat($row, $now)) {
                $buf[] = $mapped;
                $received++;
            }

            if (count($buf) >= $chunkSize) {
                $affected += $this->flushUpsert($buf);
                $buf = [];
            }
        }

        if ($buf) $affected += $this->flushUpsert($buf);

        return [$received, $affected];
    }

    protected function processJsonArrayStream(string $path, int $chunkSize): array
    {
        $now = now();
        $buf = [];
        $received = 0;
        $affected = 0;

        DB::disableQueryLog();

        if (class_exists(\JsonMachine\JsonMachine::class)) {
            // формат 1: $.cities[*]
            $it = \JsonMachine\JsonMachine::fromFile($path, '/cities');
            foreach ($it as $city) {
                if (!is_array($city)) continue;
                if ($mapped = $this->mapCityRowFlat($city, $now)) {
                    $buf[] = $mapped; $received++;
                    if (count($buf) >= $chunkSize) { $affected += $this->flushUpsert($buf); $buf = []; }
                }
            }
            if ($received === 0) {
                // формат 2: $.countries[*].regions[*].cities[*]
                $countries = \JsonMachine\JsonMachine::fromFile($path, '/countries');
                foreach ($countries as $country) {
                    if (!is_array($country)) continue;
                    $cc = $country['code'] ?? null;
                    $cid= $country['id']   ?? null;
                    foreach ($country['regions'] ?? [] as $region) {
                        foreach ($region['cities'] ?? [] as $city) {
                            if (!is_array($city)) continue;
                            if ($mapped = $this->mapCityRowNested($city, $cc, $cid, $now)) {
                                $buf[] = $mapped; $received++;
                                if (count($buf) >= $chunkSize) { $affected += $this->flushUpsert($buf); $buf = []; }
                            }
                        }
                    }
                }
            }
            if ($buf) $affected += $this->flushUpsert($buf);
            return [$received, $affected];
        }

        // Fallback (прочитать целиком) — лучше установить halaxa/json-machine
        $data = json_decode(@file_get_contents($path), true);
        if (isset($data['cities']) && is_array($data['cities'])) {
            foreach ($data['cities'] as $city) {
                if ($mapped = $this->mapCityRowFlat($city, $now)) {
                    $buf[] = $mapped; $received++;
                    if (count($buf) >= $chunkSize) { $affected += $this->flushUpsert($buf); $buf = []; }
                }
            }
        } elseif (isset($data['countries']) && is_array($data['countries'])) {
            foreach ($data['countries'] as $country) {
                $cc = $country['code'] ?? null;
                $cid= $country['id']   ?? null;
                foreach ($country['regions'] ?? [] as $region) {
                    foreach ($region['cities'] ?? [] as $city) {
                        if ($mapped = $this->mapCityRowNested($city, $cc, $cid, $now)) {
                            $buf[] = $mapped; $received++;
                            if (count($buf) >= $chunkSize) { $affected += $this->flushUpsert($buf); $buf = []; }
                        }
                    }
                }
            }
        }
        if ($buf) $affected += $this->flushUpsert($buf);
        return [$received, $affected];
    }

    /* ---------------- mapping (под вашу схему таблицы) ---------------- */

    protected function mapCityRowFlat(array $city, $now): ?array
    {
        $extId = $this->idOrNull($city['id'] ?? $city['external_id'] ?? $city['emerging_id'] ?? null);
        $name  = $this->strOrNull($city['name'] ?? $city['title'] ?? null);
        if (!$extId || !$name) {
            Log::warning('[CitiesImport] Skip row: missing id/name', ['keys' => array_keys($city)]);
            return null;
        }

        // таблица имеет и title, и name — дублируем понятным образом
        $title = $this->strOrNull($city['title'] ?? $name) ?? $name;

        return [
            'emerging_id' => $extId,                                  // ключ upsert
            'title'       => $title,
            'name'        => $name,
            'code'        => $this->strOrNull($city['code'] ?? null), // код города, если есть
            'exely_id'    => $this->intOrNull($city['exely_id'] ?? null),
            'tourmind_id' => $this->strOrNull($city['tourmind_id'] ?? null),
            'hotelstar_id'=> $this->strOrNull($city['hotelstar_id'] ?? null),
            'country_code'=> $this->strOrNull($city['country_code'] ?? null),
            'country_id'  => $this->strOrNull($city['country_id'] ?? null),
            'created_at'  => $now,
            'updated_at'  => $now,
        ];
    }

    protected function mapCityRowNested(array $city, $countryCodeFromParent, $countryIdFromParent, $now): ?array
    {
        $row = $this->mapCityRowFlat($city, $now);
        if (!$row) return null;

        // приоритет: явное поле -> значение родителя
        $row['country_code'] = $row['country_code'] ?? $this->strOrNull($countryCodeFromParent);
        $row['country_id']   = $row['country_id']   ?? $this->strOrNull($countryIdFromParent);

        return $row;
    }

    protected function flushUpsert(array $rows): int
    {
        // защитимся от мусора
        $rows = array_values(array_filter($rows, function ($r) {
            return !empty($r['emerging_id']) && !empty($r['name']);
        }));
        if (!$rows) return 0;

        $affected = City::upsert(
            $rows,
            ['emerging_id'],
            [
                'title', 'name', 'code', 'exely_id', 'tourmind_id', 'hotelstar_id',
                'country_code', 'country_id',
                'updated_at',
            ]
        );

        return is_numeric($affected) ? (int)$affected : 0;
    }

    /* ---------------- normalizers ---------------- */

    protected function scalarOrNull($v) {
        if (is_array($v)) {
            foreach ($v as $x) if (is_scalar($x) && $x !== '') return $x;
            return null;
        }
        return is_scalar($v) ? $v : null;
    }
    protected function strOrNull($v): ?string {
        $s = $this->scalarOrNull($v);
        if ($s === null) return null;
        $s = trim((string)$s);
        return $s === '' ? null : $s;
    }
    protected function idOrNull($v): ?string {
        $s = $this->scalarOrNull($v);
        return $s === null ? null : (string)$s;
    }
    protected function intOrNull($v): ?int {
        $s = $this->scalarOrNull($v);
        if ($s === null) return null;
        if ($s === '' || !is_numeric($s)) return null;
        return (int)$s;
    }
}
