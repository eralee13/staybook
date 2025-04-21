<?php

namespace App\Http\Controllers\API\V1\Tourmind;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tourmind\CreateOrder;

class CreateOrderController extends Controller
{
    protected $CreateOrder;

    public function __construct(CreateOrder $CreateOrder)
    {

        $this->CreateOrder = $CreateOrder;
        
    }

    public function fetchCreateOrder(Request $request)
    {

        $requestData = $request->all();
        $data = $this->CreateOrder->getCreateOrder($requestData);
        
        return response()->json($data);
        
    }
    
}

