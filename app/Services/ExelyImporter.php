<?php
namespace App\Services;

use App\Models\{City, Hotel, Room, Rate, CancellationRule};
use Illuminate\Support\Facades\{Http, Log, Storage};
use Illuminate\Support\Str;
class ExelyImporter
{
    public function importProperty(string $id): void
    {
        $base = rtrim(config('services.exely.base_url'), '/');

        $resp = Http::timeout(45)
            ->connectTimeout(10)
            ->retry(5, 300, throw: false)
            ->withHeaders([
                'x-api-key' => config('services.exely.key'),
                'accept'    => 'application/json'
            ])
            ->get("$base/content/v1/properties/$id");

        if (!$resp->successful()) {
            Log::warning('Exely property failed', ['id'=>$id, 'status'=>$resp->status()]);
            return;
        }

        $property = $resp->object();

        // city
        City::updateOrCreate(
            ['title' => $property->contactInfo->address->cityName ?? ''],
            [
                'exely_id' => $property->contactInfo->address->cityId ?? null,
                'code'  => Str::slug($property->contactInfo->address->cityName ?? ''),
            ]
        );

        // hotel
        $hotel = Hotel::updateOrCreate(
            ['exely_id' => $property->id],
            [
                'title'       => $property->name,
                'title_en'    => $property->name,
                'code'        => Str::slug($property->name) ?: ('hotel-'.$property->id),
                'rating'      => $property->stars ?? null,
                'city'        => $property->contactInfo->address->cityName ?? '',
                'address'     => $property->contactInfo->address->addressLine ?? '',
                'address_en'  => $property->contactInfo->address->addressLine ?? '',
                'lat'         => $property->contactInfo->address->latitude ?? null,
                'lng'         => $property->contactInfo->address->longitude ?? null,
                'phone'       => $property->contactInfo->phones[0]->phoneNumber ?? '',
                'email'       => $property->contactInfo->emails[0] ?? '',
                'checkin'     => $property->policy->checkInTime ?? null,
                'checkout'    => $property->policy->checkOutTime ?? null,
                'timezone'    => $property->timeZone->id ?? null,
                'apiName'     => 'exely',
                'status'      => 1,
            ]
        );

        // Картинка отеля — стримом, чтобы не держать всё в памяти
        if (!empty($property->images[0]->url)) {
            try {
                $stream = Http::timeout(30)->get($property->images[0]->url)->body();
                $filename = 'hotels/'.Str::uuid().'.jpg';
                Storage::disk('public')->put($filename, $stream);
                $hotel->image = $filename;
                $hotel->save();
            } catch (\Throwable $e) {
                // не критично
            }
        }

        // RoomTypes
        foreach (($property->roomTypes ?? []) as $room) {
            $fields = [
                'title'       => $room->name,
                'title_en'    => $room->name,
                'code'        => Str::slug($room->name) ?: ('room-'.$room->id),
                'hotel_id'    => $hotel->id,
                'status'      => 1,
                'description' => $room->description ?? null,
                'description_en' => $room->description ?? null,
                'area'        => $room->size->value ?? null,
                'amenities'   => collect($room->amenities ?? [])->pluck('name')->filter()->implode(','),
            ];

            // картинка комнаты
            if (!empty($room->images[0]->url)) {
                try {
                    $img = Http::timeout(30)->get($room->images[0]->url)->body();
                    $ext = pathinfo(parse_url($room->images[0]->url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    $fname = 'rooms/'.Str::uuid().'.'.$ext;
                    Storage::disk('public')->put($fname, $img);
                    $fields['image'] = $fname;
                } catch (\Throwable $e) {}
            }

            \App\Models\Room::updateOrCreate(
                ['exely_id' => $room->id],
                $fields
            );
        }

        // RatePlans (+ Cancellation)
        foreach (($property->ratePlans ?? []) as $rate) {
            try {
                $c = $rate->cancellationPolicy ?? null;

                CancellationRule::updateOrCreate(
                    ['exely_id' => $rate->id],
                    [
                        'title'                 => $rate->name,
                        'free_cancellation_days'=> null,
                        'penalty_type'          => 'fixed',
                        'penalty_amount'        => $c->penaltyAmount ?? null,
                        'cancel_policy'         => ($c && $c->freeCancellationPossible) ? 'free' : 'non-refundable',
                        'description'           => null,
                        'hotel_id'              => $hotel->id,
                        'rate_id'               => $rate->id,
                        'penalty_nights'        => null,
                    ]
                );
            } catch (\Throwable $e) {
                Log::error('Cancellation save failed: '.$e->getMessage());
            }

            // если у тебя связь rate ↔ room по room_id, пройдись по roomTypes
            foreach (($property->roomTypes ?? []) as $room) {
                Rate::updateOrCreate(
                    ['exely_id' => $rate->id, 'room_id' => $room->id],
                    [
                        'title'            => $rate->name,
                        'title_en'         => $rate->name,
                        'hotel_id'         => $hotel->id,
                        'room_id'          => $room->id,
                        'desc_en'          => $rate->description ?? null,
                        'meal_id'          => $rate->mealPlanCode ?? null,
                        'currency'         => $rate->currency ?? 'USD',
                        'price'            => $rate->price ?? 1,
                        'children_allowed' => $rate->isStayWithChildrenOnly ?? false,
                        'availability'     => $rate->availability ?? 1,
                        'free_children_age'=> 1,
                        'child_extra_fee'  => 0,
                    ]
                );
            }
        }
    }
}