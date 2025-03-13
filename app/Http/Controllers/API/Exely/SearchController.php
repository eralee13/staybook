<?php

namespace App\Http\Controllers\API\Exely;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SearchController extends Controller
{

    //Search API
    public function search_property(Request $request)
    {
        $response = Http::accept('application/json')->withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4'])->post('https://connect.test.hopenapi.com/api/search/v1/properties/room-stays/search', [
            "propertyIds" => [$request->title],
            "adults" => $request->adult,
            "childAges" => [$request->age1, $request->age2, $request->age3],
            "include" => "",
            "arrivalDate" => $request->arrivalDate,
            "departureDate" => $request->departureDate,

//            "mealPreference" => [
//                "mealType" => "MealOnly",
//                "mealsIncluded" => ["mealPlanCodes" => ["BreakFast"]],
//            ],
//            "pricePreference" => [
//                "currencyCode" => "USD",
//                "minPrice" => 0,
//                "maxPrice" => 10000,
//            ],
            //"corporateCodes" => ["string"],
        ]);
        $results = $response->object();
        dd($results);
        return view('pages.exely.search.search', compact('results'));
    }

    public function search_roomstays(Request $request)
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])
            ->get('https://connect.test.hopenapi.com/api/search/v1/properties/' . $request->city . '/room-stays?arrivalDate=' . $request->arrivalDate . '&departureDate=' . $request->departureDate . '&adults=' . $request->adults . '&includeExtraStays=false&includeExtraServices=false
');
        dd($response->object());
        $rooms = $response->object()->roomStays;
        $rooms = collect($rooms)->sortBy('total')->values()->all();

        return view('pages.exely.search.search-roomstays', compact('rooms'));
    }


    public function search_services(Request $request)
    {
        $response = Http::accept('application/json')->withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4'])->post('https://connect.test.hopenapi.com/api/search/v1/properties/500803/services', [
            "stayDates" => [
                "arrivalDateTime" => "2025-03-07T14:00",
                "departureDateTime" => "2025-03-08T12:00",
            ],
            "roomType" => ["id" => "82751", "placements" => [["code" => "AdultBed-2"]]],
            "ratePlan" => ["id" => "987657", "corporateCodes" => ["string"]],
            "guestCount" => ["adultCount" => 1, "childAges" => [2]],
        ]);
        $services = $response->object();

        return view('pages.exely.searh.search-amenities', compact('services'));
    }


    public function search_extrastays()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/search/v1/properties/500803/extra-stays',
            [
                "stayDates" => [
                    "arrivalDateTime" => "2025-03-10T14:00",
                    "departureDateTime" => "2025-03-11T12:00",
                ],
                "roomType" => ["id" => "82751", "placements" => [["code" => "AdultBed-2"]]],
                "ratePlan" => ["id" => "987657", "corporateCodes" => ["string"]],
                "guestCount" => ["adultCount" => 1, "childAges" => [5]
                ],]);
        $stays = $response->object();

        return view('pages.exely.search.search-extrastays', compact('stays'));
    }

}
