<?php

namespace App\Http\Controllers\API\V1\Tourmind;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tourmind\RoomStaticList;

class RoomStaticListController extends Controller
{
    protected $RoomStaticList;

    public function __construct(RoomStaticList $RoomStaticList)
    {

        $this->RoomStaticList = $RoomStaticList;
        
    }

    public function fetchRoomsTypes(Request $request)
    {

        $requestData = $request->all();
        $data = $this->RoomStaticList->getRoomList($requestData);
        
        return response()->json($data);
        
    }

}
