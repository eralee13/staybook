<?php

namespace App\Http\Controllers\API\V1\Tourmind;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tourmind\CheckRoomRate;

class CheckRoomRateController extends Controller
{
    protected $CheckRoomRate;

    public function __construct(CheckRoomRate $CheckRoomRate)
    {

        $this->CheckRoomRate = $CheckRoomRate;
        
    }

    public function fetchCheckRoomRate(Request $request)
    {

        $requestData = $request->all();
        $data = $this->CheckRoomRate->getCheckRoomRate($requestData);
        
        return response()->json($data);
        
    }
    
}

