<?php

namespace App\Http\Controllers\API\V1\Tourmind;

use App\Http\Controllers\Controller;
use App\Services\Tourmind\RoomStaticList;
use Illuminate\Http\Request;

class RoomStaticListController extends Controller
{
    protected $RoomStaticList;

    public function __construct(RoomStaticList $RoomStaticList)
    {

        $this->RoomStaticList = $RoomStaticList;
        
    }

    public function fetch(Request $request, RoomStaticList $svc)
    {
        // берём сырые значения (могут прийти строкой, числом или массивом)
        $piRaw = $request->input('PageIndex', 1);
        $psRaw = $request->input('PageSize', 100);

        // приводим к числу, если пришёл массив — берём первый элемент
        $pageIndex = (int) (is_array($piRaw) ? ($piRaw[0] ?? 1) : $piRaw);
        $pageSize  = (int) (is_array($psRaw) ? ($psRaw[0] ?? 100) : $psRaw);

        // границы
        $pageIndex = max(1, $pageIndex);
        $pageSize  = min(max(1, $pageSize), 500);

        // вызов сервиса со строгими int
        $result = $svc->fetchRoomStaticList($pageIndex, $pageSize);

        return response()->json($result);
    }

}
