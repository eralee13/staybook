<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Rate;

class SyncExelyHotels extends Command
{
    protected $signature = 'sync:exely-hotels';
    protected $description = 'Синхронизация отелей и тарифов из Exely API';

    public function handle()
    {
        $response = Http::timeout(300)
            ->connectTimeout(15)
            ->retry(5)
            ->withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
            ->get(config('services.exely.base_url') . 'content/v1/properties');

        $properties = $response->object();

        if ($properties->properties != null) {
            foreach ($properties->properties as $hotel) {
                $response = Http::withHeaders([
                    'x-api-key' => config('services.exely.key'),
                    'accept' => 'application/json'
                ])->get(config('services.exely.base_url') . 'content/v1/properties/' . $hotel->id);

                $property = $response->object();
                $exely_hotel = Hotel::where('exely_id', $property->id)->first();

                if ($exely_hotel) {
                    $exely_hotel->update([
                        'title' => $property->name,
                        'title_en' => $property->name,
                        'code' => Str::slug($property->name),
                        'description' => $property->description,
                        'description_en' => $property->description,
                        'rating' => $property->stars ?? null,
                        'city' => $property->contactInfo->address->cityName,
                        'address' => $property->contactInfo->address->addressLine,
                        'address_en' => $property->contactInfo->address->addressLine,
                        'lat' => $property->contactInfo->address->latitude,
                        'lng' => $property->contactInfo->address->longitude,
                        'phone' => $property->contactInfo->phones[0]->phoneNumber ?? '',
                        'email' => $property->contactInfo->emails[0],
                        'checkin' => $property->policy->checkInTime,
                        'checkout' => $property->policy->checkOutTime,
                        'early_in' => '',
                        'late_out' => '',
                        'timezone' => $property->timeZone->id,
                        'status' => 1,
                        'exely_id' => $property->id,
                    ]);
                } else {
                    $filename = null;
                    if (!empty($property->images)) {
                        $url = $property->images[0]->url;
                        $imageContents = @file_get_contents($url);
                        if ($imageContents) {
                            $filename = 'hotels/' . Str::uuid() . '.jpg';
                            Storage::disk('public')->put($filename, $imageContents);
                        }
                    }

                    Hotel::create([
                        'title' => $property->name,
                        'title_en' => $property->name,
                        'code' => Str::slug($property->name),
                        'description' => $property->description,
                        'description_en' => $property->description,
                        'image' => $filename,
                        'rating' => $property->stars ?? null,
                        'city' => $property->contactInfo->address->cityName,
                        'address' => $property->contactInfo->address->addressLine,
                        'address_en' => $property->contactInfo->address->addressLine,
                        'lat' => $property->contactInfo->address->latitude,
                        'lng' => $property->contactInfo->address->longitude,
                        'phone' => $property->contactInfo->phones[0]->phoneNumber ?? '',
                        'email' => $property->contactInfo->emails[0],
                        'checkin' => $property->policy->checkInTime,
                        'checkout' => $property->policy->checkOutTime,
                        'early_in' => '',
                        'late_out' => '',
                        'timezone' => $property->timeZone->id,
                        'status' => 1,
                        'exely_id' => $property->id,
                    ]);
                }

                foreach ($property->ratePlans as $rate) {
                    if (empty($rate->roomTypeIds)) {
                        Log::warning('Rate план без roomTypeIds', ['rate_id' => $rate->id]);
                        continue;
                    }

                    foreach ($rate->roomTypeIds as $roomTypeId) {
                        $room = Room::where('exely_id', $roomTypeId)->first();
                        if (!$room) continue;

                        Rate::updateOrCreate(
                            ['exely_id' => $rate->id],
                            [
                                'title' => $rate->name,
                                'title_en' => $rate->name,
                                'hotel_id' => $room->hotel_id,
                                'room_id' => $room->id,
                                'desc_en' => $rate->description,
                                'currency' => $rate->currency,
                                'price' => $rate->price ?? 1,
                                'cancellation_rule_id' => $rate->cancellationRuleId ?? null,
                                'bed_type' => $room->title,
                                'children_allowed' => $rate->isStayWithChildrenOnly ?? false,
                                'availability' => 1,
                                'free_children_age' => 1,
                                'child_extra_fee' => 10,
                            ]
                        );
                    }
                }
            }
        }

        $this->info('✅ Exely sync completed at ' . now());
    }
}