<?php

namespace App\Http\Controllers\API\Exely;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class SearchController extends Controller
{

    //Search API
    public function search_property(Request $request)
    {
        $hotels = [];
        //$city_hotels = Hotel::where('city', $request->title)->get();
        $cities = City::cacheFor(now()->addHours(24))->where('exely_id', $request->city)->get();
        foreach ($cities as $city) {
            $properties = Hotel::where('city', $city->title)->get();
            foreach ($properties as $hotel) {
                $hotels[] = "$hotel->exely_id";
            }
        }

        if($request->age1 != null && $request->age2 != null && $request->age3 != null){
            $age1 = $request->age1;
            $age2 = $request->age2;
            $age3 = $request->age3;
            $child_array = [$age1, $age2, $age3];
        }
        elseif($request->age1 != null && $request->age2 != null){
            $age1 = $request->age1;
            $age2 = $request->age2;
            $child_array = [$age1, $age2];
        }
        elseif($request->age1 != null){
            $age1 = $request->age1;
            $child_array = [$age1];
        }
        else {
            $child_array = [1];
        }
        //dd($child_array);

        $response = Http::accept('application/json')->withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4'])->post('https://connect.test.hopenapi.com/api/search/v1/properties/room-stays/search', [
            "propertyIds" => $hotels,
            "adults" => $request->adult,
            "childAges" => $child_array,
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

        return view('pages.search', compact('results', 'request'));
    }

    public function search_roomstays(Request $request)
    {
        $childs = explode(',', $request->childAges);
        foreach ($childs as $child) {
            $items[] = '&childAges=' . $child;
        }
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])
            ->get('https://connect.test.hopenapi.com/api/search/v1/properties/' . $request->propertyId . '/room-stays?arrivalDate=' . $request->arrivalDate . '&departureDate=' . $request->departureDate . '&adults=' . $request->adults . implode($items) . '&includeExtraStays=false&includeExtraServices=false');

        $rooms = $response->object()->roomStays;
        //dd($rooms);
        $rooms = collect($rooms)->sortBy('total')->values()->all();

        return view('pages.exely.search.search-roomstays', compact('rooms', 'request'));
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
