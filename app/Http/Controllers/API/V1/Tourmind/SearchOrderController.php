<?php

namespace App\Http\Controllers\API\V1\Tourmind;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tourmind\SearchOrder;

class SearchOrderController extends Controller
{
    protected $SearchOrder;

    public function __construct(SearchOrder $SearchOrder)
    {

        $this->SearchOrder = $SearchOrder;
        
    }

    public function fetchSearchOrder(Request $request)
    {

        $requestData = $request->all();
        $data = $this->SearchOrder->getSearchOrder($requestData);
        
        return response()->json($data);
        
    }
    
}

