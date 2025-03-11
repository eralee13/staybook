<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RateResource;
use App\Models\Rate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class RateController extends Controller
{
    /**
     * @return Collection
     */
    public function index()
    {
        return RateResource::collection(Rate::all());
    }


    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id){
        try {
            $rate = Rate::findOrFail($id);
            return response()->json($rate, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Rate not found'], 404);
        }
    }

//    public function store()
//    {
//
//    }
//
//    /**
//     * @param Request $request
//     * @param Rate $category
//     * @return Rate
//     */
//    public function update(Request $request, Rate $category)
//    {
//        $category->update($request->all());
//        return $category;
//    }
//
//    /**
//     * @param Rate $category
//     * @return Application|ResponseFactory|\Illuminate\Foundation\Application|Response
//     */
//    public function destroy(Rate $category)
//    {
//        $category->delete();
//        return response(null, 204);
//    }
}
