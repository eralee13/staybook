<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;


class RoomController extends Controller
{
    /**
     * @return Collection
     */
    public function index()
    {
        return Room::all();
    }

    /**
     * @param Room $room
     * @return Room
     */
    public function show(Room $room)
    {
        if($room == null){
            abort(404);
        }
        return $room;
    }

    public function store(Request $request)
    {
        Room::create($request->all());
        return response('Room created successfully.', 201);
    }

    public function update(Request $request, Room $room)
    {
        $room->update($request->all());
        return $room;
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return response(null, 204);
    }
}
