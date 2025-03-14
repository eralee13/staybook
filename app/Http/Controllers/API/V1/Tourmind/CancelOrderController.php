<?php

namespace App\Http\Controllers\API\V1\Tourmind;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tourmind\CancelOrder;

class CancelOrderController extends Controller
{
    protected $CancelOrder;

    public function __construct(CancelOrder $CancelOrder)
    {

        $this->CancelOrder = $CancelOrder;
        
    }

    public function fetchCancelOrder(Request $request)
    {

        $requestData = $request->all();
        $data = $this->CancelOrder->getCancelOrder($requestData);
        
        return response()->json($data);
        
    }
    
}

