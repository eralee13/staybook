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
        if($status == 3) {
            $status = null;
        }else{
            $status = $status;
        }

        if ($show_result == 'all') {
            $show_result = 500000;
        }elseif($show_result == '0'){
            $show_result = 10;
        }else{
            $show_result = $show_result;
        }

        if ($s_query == '0') {
            $s_query = null;
        }else{
            $s_query = $s_query;
        }

        $rooms = Room::query();



        if ($s_query !== null || isset($status) || $show_result) {
            $rooms = $rooms->where(function($query) use ($status, $s_query) {
                if (isset($status)) {
                    $query->where('status', $status);
                }
                if ($s_query !== null) {
                    $query->where(function($query) use ($s_query) {
                        $query->where('title', 'like', '%'.$s_query.'%');
                    });
                }
            });
        }
        $hotel = $request->session()->get('hotel_id');
        $rooms = $rooms->where('hotel_id', $hotel)->paginate(intval($show_result));

        return view('auth.rooms.index', compact('rooms', 'status','show_result', 's_query'));
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

        unset($params['bed']);
        if($request->has('bed')){
            $params['bed'] = implode(', ', $request->bed);
        }

        unset($params['amenities']);
        if($request->has('amenities')){
            $params['amenities'] = implode(', ', $request->services);
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
        $cats = CategoryRoom::all();
        $services = explode(', ', $room->services);
        $images = Image::where('room_id', $room->id)->get();
        return view('auth.rooms.form', compact('room', 'hotels', 'images', 'hotel', 'services', 'cats'));
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
            $params['amenities'] = implode(', ', $request->services);
        }

        // image
        unset($params['image']);
        if ($request->has('image')) {
            Storage::delete($room->image);
            $params['image'] = $request->file('image')->store('rooms');
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
        Storage::delete($room->image);
        Rate::where('room_id', $room->id)->get();
        Accommodation::where('room_id', $room->id)->get();
        $images = Image::where('room_id', $room->id)->get();
        foreach ($images as $image){
            Storage::delete($image->image);
        }
        DB::table('images')->where('room_id', $room->id)->delete();
        //Mail::to('info@timmedia.store')->send(new RoomDeleteMail($room));

        session()->flash('success', 'Room ' . $room->title . ' deleted');
        return redirect()->route('rooms.index');
    }



}
