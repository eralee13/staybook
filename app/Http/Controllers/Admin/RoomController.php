<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Mail\RoomCreateMail;
use App\Mail\RoomDeleteMail;
use App\Mail\RoomUpdateMail;
use App\Models\CategoryRoom;
use App\Models\Rate;
use App\Models\Accommodation;
use App\Models\Hotel;
use App\Models\Image;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-room|edit-room|delete-room', ['only' => ['index','show']]);
        $this->middleware('permission:create-room', ['only' => ['create','store']]);
        $this->middleware('permission:edit-room', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-room', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $status=null, $show_result = null,  $s_query = null)
    {

        $hotel_id = $request->session()->get('hotel_id');
        $hotel = Hotel::where('id', $hotel_id)->firstOrFail();
        $rooms = Room::where('hotel_id', $hotel_id)->paginate(20);

        return view('auth.rooms.index', compact('rooms', 'status','show_result', 's_query', 'hotel'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $hotels = Hotel::all();
        return view('auth.rooms.form', compact('hotels', 'hotel'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoomRequest $request, Room $room)
    {
        $request['code'] = Str::slug($request->title);
        $params = $request->all();

//        unset($params['bed']);
//        if($request->has('bed')){
//            $params['bed'] = implode(', ', $request->bed);
//        }

        unset($params['amenities']);
        if($request->has('amenities')){
            $params['amenities'] = implode(', ', $request->amenities);
        }

        unset($params['image']);
        if($request->has('image')){
            $path = $request->file('image')->store('rooms');
            $params['image'] = $path;
        }
        $room = Room::create($params);

        $images = $request->file('images');
        if ($request->hasFile('images')) :
            foreach ($images as $image):
                $image = $image->store('rooms');
                DB::table('images')->insert(
                    array(
                        'image'=>  $image,
                        'room_id' => $room->id,
                    )
                );
            endforeach;
        endif;

        //Mail::to('info@timmedia.store')->send(new RoomCreateMail($request));
        session()->flash('success', 'Room ' . $request->title . ' created');
        return redirect()->route('rooms.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        $images = Image::where('room_id', $room->id)->get();
        return view('auth.rooms.show', compact('room', 'images'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Room $room)
    {
        $hotel = $request->session()->get('hotel_id');
        $hotels = Hotel::all();
        $amenities = explode(', ', $room->amenities);
        $images = Image::where('room_id', $room->id)->get();
        return view('auth.rooms.form', compact('room', 'hotels', 'images', 'hotel', 'amenities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoomRequest $request, Room $room)
    {
        $request['code'] = Str::slug($request->title);
        $params = $request->all();

        // bed
//        unset($params['bed']);
//        if($request->has('bed')){
//            $params['bed'] = implode(', ', $request->bed);
//        }

        // amenities
        unset($params['amenities']);
        if($request->has('amenities')){
            $params['amenities'] = implode(', ', $request->amenities);
        }

        // image
        unset($params['image']);
        if ($request->has('image')) {
            //Storage::delete($room->image);
            $params['image'] = $request->file('image')->store('rooms');
            DB::table('images')
                ->where('room_id', $room->id)
                ->updateOrInsert(['room_id' => $room->id, 'image' => $params['image']]);
        }

        //images
        unset($params['images']);
        $images = $request->file('images');
        if ($request->hasFile('images')) {
//            $dimages = Image::where('room_id', $room->id)->get();
//            if ($dimages != null) {
//                foreach ($dimages as $image){
//                    Storage::delete($image->image);
//                }
//                DB::table('images')->where('room_id', $room->id)->delete();
//            }
            foreach ($images as $image):
                $image = $image->store('rooms');
                DB::table('images')
                    ->where('room_id', $room->id)
                    ->updateOrInsert(['room_id' => $room->id, 'image' => $image]);
            endforeach;
        }

        $room->update($params);
        //Mail::to('info@timmedia.store')->send(new RoomUpdateMail($request));
        session()->flash('success', 'Room ' . $request->title . ' updated');
        return redirect()->route('rooms.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        $room->delete();
        if($room->image){
            Storage::delete($room->image);
        }
        Rate::where('room_id', $room->id)->get();
        $images = Image::where('room_id', $room->id)->get();
        if($images->isNotEmpty()){
            foreach ($images as $image){
                Storage::delete($image->image);
            }
            DB::table('images')->where('room_id', $room->id)->delete();
        }
        //Mail::to('info@timmedia.store')->send(new RoomDeleteMail($room));
        session()->flash('success', 'Room ' . $room->title . ' deleted');
        return redirect()->route('rooms.index');
    }



}
