<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryRoomResource;
use App\Models\CategoryRoom;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryRoomController extends Controller
{

    /**
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return CategoryRoomResource::collection(CategoryRoom::all());
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id){
        try {
            $category = CategoryRoom::findOrFail($id);
            return response()->json($category, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Category Room not found'], 404);
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
