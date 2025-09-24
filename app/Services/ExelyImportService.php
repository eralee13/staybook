<?php
namespace App\Services;

use App\Models\CancellationRule;
use App\Models\City;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Rate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
class ExelyImportService
{
    public function handle(): void
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        $response = Http::timeout(300)
            ->connectTimeout(15)
            ->retry(5)
            ->withHeaders([
                'x-api-key' => config('services.exely.key'),
                'accept' => 'application/json'
            ])
            ->get(config('services.exely.base_url') . 'content/v1/properties');

        $properties = $response->object()->properties ?? [];

        foreach ($properties as $hotelData) {
            try {
                $property = Http::withHeaders([
                    'x-api-key' => config('services.exely.key'),
                    'accept' => 'application/json'
                ])->get(config('services.exely.base_url') . 'content/v1/properties/' . $hotelData->id)->object();

                $this->importCity($property);
                $hotel = $this->importHotel($property);
                $this->importRooms($property, $hotel);
                $this->importRates($property, $hotel);
            } catch (\Throwable $e) {
                Log::error('Ошибка при импорте отеля ' . $hotelData->id . ': ' . $e->getMessage());
                continue;
            }
        }
    }

    protected function importCity($property)
    {
        City::updateOrCreate(
            ['title' => $property->contactInfo->address->cityName],
            [
                'exely' => $property->contactInfo->address->cityId,
                'code' => Str::slug($property->contactInfo->address->cityName),
            ]
        );
    }

    protected function importHotel($property)
    {
        $fields = [
            'title' => $property->name,
            'title_en' => $property->name,
            'code' => Str::slug($property->name),
            'rating' => $property->stars ?? null,
            'city' => $property->contactInfo->address->cityName,
            'address' => $property->contactInfo->address->addressLine,
            'address_en' => $property->contactInfo->address->addressLine,
            'lat' => $property->contactInfo->address->latitude,
            'lng' => $property->contactInfo->address->longitude,
            'phone' => $property->contactInfo->phones[0]->phoneNumber ?? '',
            'email' => $property->contactInfo->emails[0] ?? '',
            'checkin' => $property->policy->checkInTime ?? null,
            'checkout' => $property->policy->checkOutTime ?? null,
            'early_in' => '',
            'late_out' => '',
            'timezone' => $property->timeZone->id ?? null,
            'status' => 1,
        ];

        // Только если description не пустой
        if (!empty($property->description)) {
            $fields['description'] = $property->description;
            $fields['description_en'] = $property->description;
        }

        // Удобства (amenities)
        $amenities = collect($property->amenities ?? [])->pluck('name')->filter()->implode(',');
        if (!empty($amenities)) {
            $fields['amenities'] = $amenities;
        }

        // Загрузка изображения, если оно есть
        if (!empty($property->images)) {
            $url = $property->images[0]->url;
            try {
                $imageContents = file_get_contents($url);
                $filename = 'hotels/' . Str::uuid() . '.jpg';
                Storage::disk('public')->put($filename, $imageContents);
                $fields['image'] = $filename;
            } catch (\Exception $e) {
                // Не записываем image, если ошибка
            }
        }
        return Hotel::updateOrCreate(
            ['exely_id' => $property->id],
            $fields
        );
    }

    protected function importRooms($property, $hotel)
    {
        foreach ($property->roomTypes ?? [] as $room) {
            $fields = [
                'title' => $room->name,
                'title_en' => $room->name,
                'code' => Str::slug($room->name),
                'hotel_id' => $hotel->id,
                'status' => 1,
            ];

            // Загрузка изображения, если есть
            if (!empty($room->images[0]->url)) {
                try {
                    $imageContents = file_get_contents($room->images[0]->url);
                    $ext = pathinfo(parse_url($room->images[0]->url, PHP_URL_PATH), PATHINFO_EXTENSION);
                    $filename = 'rooms/' . Str::uuid() . '.' . ($ext ?: 'jpg');
                    Storage::disk('public')->put($filename, $imageContents);
                    $fields['image'] = $filename;
                } catch (\Exception $e) {
                    // Не добавляем image
                }
            }

            // Description
            if (!empty($room->description)) {
                $fields['description'] = $room->description;
                $fields['description_en'] = $room->description;
            }

            // Площадь (area)
            if (!empty($room->size->value)) {
                $fields['area'] = $room->size->value;
            }

            // Категория (если есть)
            if (!empty($room->categoryName)) {
                $fields['category_id'] = $room->categoryName;
            }

            // Удобства (amenities)
            $amenities = collect($room->amenities ?? [])->pluck('name')->filter()->implode(',');
            if (!empty($amenities)) {
                $fields['amenities'] = $amenities;
            }

            Room::updateOrCreate(
                ['exely_id' => $room->id],
                $fields
            );
        }
    }

    protected function importRates($property, $hotel)
    {
        foreach ($property->ratePlans ?? [] as $rate) {
            foreach ($property->roomTypes ?? [] as $room) {

                Log::info('⛳ Сохраняем cancellation_rule', [
                    'exely_id' => $rate->id,
                    'penaltyAmount' => $rate->cancellationPolicy->penaltyAmount ?? null,
                    'hotel_id' => $hotel->id,
                ]);

                try {

                    $cancellationPolicy = $rate->cancellationPolicy ?? null;

                    CancellationRule::updateOrCreate(
                        ['exely_id' => $rate->id],
                        [
                            'title' => $rate->name,
                            'free_cancellation_days' => null,
                            'penalty_type' => 'fixed',
                            'penalty_amount' => $cancellationPolicy->penaltyAmount ?? null,
                            'cancel_policy' => $cancellationPolicy && $cancellationPolicy->freeCancellationPossible
                                ? 'free'
                                : 'non-refundable',
                            'description' => null,
                            'hotel_id' => $hotel->id,
                            'rate_id' => $rate->id,
                            'penalty_nights' => null,
                        ]
                    );

                } catch (\Throwable $e) {
                    Log::error('❌ Ошибка сохранения cancellation_rule: ' . $e->getMessage());
                }


                Rate::updateOrCreate(
                    ['exely_id' => $rate->id, 'room_id' => $room->id],
                    [
                        'title' => $rate->name,
                        'title_en' => $rate->name,
                        'hotel_id' => $hotel->id,
                        'room_id' => $room->id,
                        'desc_en' => $rate->description,
                        'meal_id' => $rate->mealPlanCode ?? null,
                        'currency' => $rate->currency,
                        'price' => $rate->price ?? 1,
                        //'cancellation_rule_id' => $rate->cancellationRuleId ?? null,
                        'bed_type' => $room->bed_type ?? null,
                        'children_allowed' => $rate->isStayWithChildrenOnly ?? false,
                        'availability' => $rate->availability ?? 1,
                        'free_children_age' => 1,
                        'child_extra_fee' => 0,
                    ]
                );
            }
        }
    }
}