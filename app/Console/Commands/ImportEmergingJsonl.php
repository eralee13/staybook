<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Hotel;
use App\Models\Amenity;

class ImportEmergingJsonl extends Command
{
    protected $signature = 'emerging:import-jsonl 
        {path=storage/app/partner_feed_en_v3.jsonl : Path to JSONL}
        {--offset=0 : Start line offset (0-based)}
        {--limit=5000 : Max lines to process in this run}';

    protected $description = 'Import Emerging hotels from JSONL (streamed, batched, resumable).';

    // app/Console/Commands/ImportEmergingJsonl.php

    public function handle(): int
    {
        $path   = $this->argument('path');                // storage/app/partner_feed_en_v3.jsonl
        $offset = (int)$this->option('offset');           // --offset=0
        $limit  = (int)$this->option('limit');            // --limit=500

        $file = new \SplFileObject($path, 'r');
        $file->setFlags(\SplFileObject::DROP_NEW_LINE | \SplFileObject::SKIP_EMPTY);

        $this->info("Start at line {$offset}, limit {$limit}…");

        $current   = 0;
        $processed = 0;

        // проматываем до offset
        while (!$file->eof() && $current < $offset) { $file->fgets(); $current++; }

        while (!$file->eof() && ($limit === 0 || $processed < $limit)) {
            $line = trim($file->fgets());
            if ($line === '') { $current++; continue; }

            $row = json_decode($line, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->warn("Skip invalid JSON at #{$current}");
                $current++;
                continue;
            }

            try {
                $this->processHotel($row);   // см. ниже
                $processed++;
            } catch (\Throwable $e) {
                $this->error("Row #{$current} failed: ".$e->getMessage());
            }

            $current++;
        }

        $this->info("Done. Processed: {$processed}. Next offset: {$current}");
        $this->line("Next run: php artisan emerging:import-jsonl '{$path}' --offset={$current} --limit={$limit}");

        return self::SUCCESS;
    }

    // app/Console/Commands/ImportEmergingJsonl.php (внутри класса)

    protected function processHotel(array $h): void
    {
        // базовая защита
        if (empty($h['hid']) || empty($h['name'])) return;

        // адреса/регион
        $region      = $h['region'] ?? [];
        $city        = (string)($region['name'] ?? '');
        $countryCode = (string)($region['country_code'] ?? '');

        // рейтинг
        $star   = (int)($h['star_rating'] ?? 0);

        // описания
        $descrHotel = $this->pickParagraphs($h['description_struct'] ?? [], 'At the hotel');
        $descrRoom  = $this->pickParagraphs($h['description_struct'] ?? [], 'Room amenities');

        // удобства (группы)
        $amenityGroups = $h['amenity_groups'] ?? [];
        $amenHotel = $this->pickAmenities($amenityGroups, 'Services and amenities');
        $amenRooms = $this->pickAmenities($amenityGroups, 'Rooms');

        // время заезда/выезда
        $checkin  = $h['check_in_time']  ?? null;
        $checkout = $h['check_out_time'] ?? null;

        // координаты
        $lat = $h['latitude']  ?? null;
        $lng = $h['longitude'] ?? null;

        // создаём/обновляем отель
        /** @var \App\Models\Hotel $hotel */
        $hotel = \App\Models\Hotel::updateOrCreate(
            ['emerging_id' => (int)$h['hid']],
            [
                'code'           => (string)($h['id'] ?? ''),
                'title'          => (string)$h['name'],
                'title_en'       => (string)$h['name'],
                'type'           => (string)($h['kind'] ?? ''),
                'rating'         => $star,
                'address_en'     => (string)($h['address'] ?? ''),
                'city'           => $city,
                'country_code'   => $countryCode, // если колонка есть
                'lat'            => $lat,
                'lng'            => $lng,
                'checkin'        => $checkin,
                'checkout'       => $checkout,
                'phone'          => $h['phone'] ?? null,
                'email'          => $h['email'] ?? null,
                'description_en' => $descrHotel,
                'image'          => '',          // проставишь первую картинку, если надо
                'status'         => 1,
                'user_id'        => 1,
            ]
        );

        // amenities (одна запись на отель)
        \App\Models\Amenity::updateOrCreate(
            ['hotel_id' => $hotel->id],
            [
                'title'    => 'Services',
                'services' => $amenHotel, // строка с запятыми
            ]
        );

        // базовая Room, если нужна
        \App\Models\Room::updateOrCreate(
            ['hotel_id' => $hotel->id, 'title_en' => 'Standard room'],
            [
                'title'         => 'Standard room',
                'title_en'      => 'Standard room',
                'services'      => $amenRooms,     // строка с запятыми
                'description_en'=> $descrRoom,
            ]
        );

        // изображения — складываем ссылки (без скачивания)
        $this->saveImagesFromExt($hotel->id, (array)($h['images_ext'] ?? []), 20, '1024x768');
    }

// собрать параграфы по title
    protected function pickParagraphs(array $blocks, string $title): string
    {
        foreach ($blocks as $blk) {
            if (($blk['title'] ?? '') === $title) {
                $p = $blk['paragraphs'] ?? [];
                return implode("\n", array_filter(array_map('trim', (array)$p)));
            }
        }
        return '';
    }

// собрать список удобств из группы
    protected function pickAmenities(array $groups, string $groupName): string
    {
        foreach ($groups as $g) {
            if (($g['group_name'] ?? '') === $groupName) {
                $a = (array)($g['amenities'] ?? []);
                return implode(', ', array_filter(array_map('trim', $a)));
            }
        }
        return '';
    }

// сохранить ссылки картинок в таблицу images (без скачивания)
    protected function saveImagesFromExt(int $hotelId, array $imagesExt, int $limit = 20, string $size = '1024x768'): void
    {
        $i = 0;
        foreach ($imagesExt as $img) {
            $urlTpl = $img['url'] ?? null;
            if (!$urlTpl) continue;
            $i++; if ($i > $limit) break;

            $finalUrl = str_replace('{size}', $size, $urlTpl);

            \App\Models\Image::updateOrCreate(
                [
                    'hotel_id' => $hotelId,
                    'image'    => $finalUrl, // храним прямую ссылку
                ],
                [
                    'caption'  => (string)($img['category_slug'] ?? ''),
                ]
            );
        }
    }

}