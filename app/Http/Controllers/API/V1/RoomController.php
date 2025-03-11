<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RoomResource;
use App\Models\Room;
use App\Models\Rule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class RoomController extends Controller
{
    /**
     * @return Collection
     */
    public function index()
    {
        return RoomResource::collection(Room::all());
    }

    public function show($id){
        try {
            $room = Room::findOrFail($id);
            return response()->json($room);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Room not found'], 404);
        }
    }

//    /**
//     * @param Request $request
//     * @return Application|ResponseFactory|\Illuminate\Foundation\Application|Response
//     */
//    public function store(Request $request)
//    {
//        Room::create($request->all());
//        return response('Room created successfully.', 201);
//    }
//
//    /**
//     * @param Request $request
//     * @param Room $room
//     * @return Room
//     */
//    public function update(Request $request, Room $room)
//    {
//        $room->update($request->all());
//        return $room;
//    }
//
//    /**
//     * @param Room $room
//     * @return Application|ResponseFactory|\Illuminate\Foundation\Application|Response
//     */
//    public function destroy(Room $room)
//    {
//        $room->delete();
//        return response(null, 204);
//    }
}
