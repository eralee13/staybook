<?php

namespace App\Http\Controllers\API\V1_1;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HotelController extends Controller
{
    public function index(): StreamedResponse
    {
        return new StreamedResponse(function () {
            Hotel::query()
                ->with(['images:id,hotel_id,image', 'amenity:id,hotel_id,services'])
                ->orderBy('title')
                ->chunk(300, function ($chunk) {
                    foreach ($chunk as $h) {

                        $row = [
                            'id'              => (string)($h->code ?? $h->id),
                            'name'            => (string)($h->title_en ?: $h->title),
                            'description'     => null,
                            'geo_coordinates' => [
                                'latitude'  => $h->lat !== null ? (float)$h->lat : null,
                                'longitude' => $h->lng !== null ? (float)$h->lng : null,
                            ],
                            'address'         => '',
                            'currency'        => 'USD',
                            'stars'           => 3,
                            'images'          => collect($h->images)->take(20)->map(fn($img) => [
                                'url' => url(Storage::url($img->image)),
                            ])->values(),
                            'amenities'       => $h->amenity && $h->amenity->services
                                ? array_values(array_filter(array_map('trim', explode(',', (string)$h->amenity->services))))
                                : [],
                        ];

                        echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
                        @ob_flush();
                        flush();
                    }
                });
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=utf-8',
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}