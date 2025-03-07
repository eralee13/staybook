<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function create(Image $image)
    {
        return view('auth.rooms.form', compact('image'));
    }

    public function store(Request $request, Image $image, Room $room){
        $params = $request->all();
        $params['room_id'] = $request->room->id;
        $image = Image::create($params);
        session()->flash('success', $request->room->title . ' добавлен' );
        return redirect()->route('rooms.index', $room);
    }

    public function destroy(Image $image)
    {
        $image->delete();
        Storage::delete($image);
        session()->flash('success', 'Image deleted');
        return redirect()->back();
    }
}
