<?php
namespace App\Http\Controllers\API\HotelStar;

use Illuminate\Http\Request;
use App\Services\HotelStarService;

class HotelStarController extends Controller
{
    protected HotelStarService $hotelStar;

    public function __construct(HotelStarService $hotelStar)
    {
        $this->hotelStar = $hotelStar;
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'region_id' => 'required_without:hotel_ids|integer',
            'hotel_ids' => 'array',
            'check_in' => 'required|date',
            'check_out' => 'required|date',
            'adults' => 'required|integer|min:1',
            'children' => 'array',
            'currency' => 'string|in:RUB,USD,EUR',
            '3d_hotelstar' => 'string|nullable',
        ]);

        return response()->json($this->hotelStar->search($data));
    }

    public function actualize(Request $request)
    {
        $data = $request->validate([
            'search_data' => 'required|array',
            'search_item.hash' => 'required|string',
            'search_item.provider_id' => 'required|integer',
        ]);

        return response()->json($this->hotelStar->actualize(
            $data['search_data'],
            $data['search_item']
        ));
    }

    public function book(Request $request)
    {
        $data = $request->validate([
            'partner_order_id' => 'required|string',
            'partner_price' => 'required|numeric',
            'email' => 'required|email',
            'phone' => 'required|string',
            'persons' => 'required|array|min:1',
            'persons.*.name' => 'required|string',
            'persons.*.surname' => 'required|string',
            'search_data' => 'required|array',
            'search_item.hash' => 'required|string',
            'search_item.provider_id' => 'required|integer',
            'meals' => 'array|nullable',
            'extras' => 'array|nullable',
            'client_remarks' => 'string|nullable',
        ]);

        return response()->json($this->hotelStar->book($data));
    }

    public function cancel(Request $request)
    {
        $data = $request->validate([
            'partner_order_id' => 'nullable|string',
            'order_id' => 'nullable|string',
            'partner_penalty' => 'nullable|numeric',
        ]);

        return response()->json($this->hotelStar->cancel($data));
    }

    public function info(Request $request)
    {
        $data = $request->validate([
            'partner_order_id' => 'nullable|string',
            'order_id' => 'nullable|string',
        ]);

        return response()->json($this->hotelStar->info($data));
    }

    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'nullable|string',
            'user_name' => 'required|string',
            'date' => 'required|date_format:Y-m-d H:i:s',
            'text' => 'required|string',
            'type' => 'string|in:text,media,file,url',
        ]);

        return response()->json($this->hotelStar->sendMessage($data));
    }

    public function messageList(Request $request)
    {
        $data = $request->validate([
            'partner_order_id' => 'nullable|string',
            'order_id' => 'nullable|string',
        ]);

        return response()->json($this->hotelStar->messageList($data));
    }
}
