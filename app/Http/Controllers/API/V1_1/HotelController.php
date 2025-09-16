<?php

namespace App\Http\Controllers\API\V1_1;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HotelController extends Controller
{
    /**
     * GET /api/v1.1/hotels
     * Возвращает NDJSON (по строке = один объект HotelStatic).
     */
    public function index(Request $request): StreamedResponse
    {
        // НЕ указываем Content-Encoding, если не сжимаем в PHP.
        // Gzip лучше делать на уровне nginx/Apache.
        $headers = [
            'Content-Type' => 'application/x-ndjson; charset=utf-8',
        ];

        return response()->stream(function () {
            Hotel::query()
                ->with(['images:id,hotel_id,image', 'amenity:id,hotel_id,services'])
                ->select(['id','code','title','title_en','address','city','lat','lng','rating'])
                ->orderBy('title')
                ->chunk(500, function ($chunk) {
                    foreach ($chunk as $h) {
                        $row = [
                            'id'                  => (string)($h->code ?? $h->id),
                            'name'                => (string)($h->title_en ?: $h->title),
                            'description'         => null, // можно заполнить при наличии
                            'geo_coordinates'     => [
                                'latitude' => $h->lat !== null ? (float) $h->lat : null,
                                'longitude' => $h->lng !== null ? (float) $h->lng : null,
                            ],
                            'address'             => '',
                            'currency'            => 'USD', // ISO 4217
                            'stars'               => 3,
                            'images'              => collect($h->images)->take(20)->map(function ($img) {
                                return [
                                    'url' => url(Storage::url($img->image)),
                                ];
                            })->values(),

                            'amenities'           => $h->amenity && $h->amenity->services
                                ? array_values(array_filter(array_map('trim', explode(',', (string)$h->amenity->services))))
                                : [],
                        ];

                        // Печатаем одну NDJSON-строку
                        echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
                        @ob_flush();
                        flush();
                    }
                });
        }, 200, $headers);
    }
}