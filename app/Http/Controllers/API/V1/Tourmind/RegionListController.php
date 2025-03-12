<?php
namespace App\Http\Controllers\API\V1\Tourmind;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tourmind\RegionList;

class RegionListController extends Controller
{
    protected $RegionList;

    public function __construct(RegionList $RegionList)
    {

        $this->RegionList = $RegionList;
        
    }

    public function fetchRegions(Request $request)
    {

        $requestData = $request->all();
        $data = $this->RegionList->getRegionList($requestData);
        
        return response()->json($data);
        
    }
}
