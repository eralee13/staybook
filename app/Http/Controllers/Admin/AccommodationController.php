<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccommodationRequest;
use App\Models\Accommodation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AccommodationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-child|edit-child|delete-child', ['only' => ['index','show']]);
        $this->middleware('permission:create-child', ['only' => ['create','store']]);
        $this->middleware('permission:edit-child', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-child', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $childs = Accommodation::where('hotel_id', $hotel)->paginate(10);
        $rooms = Room::where('hotel_id', $hotel)->get();
        return view('auth.accommodations.index', compact('childs', 'rooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $childs = Accommodation::where('hotel_id', $hotel)->get();
        foreach ($childs as $child) {
            $data[] = $child->room_id;
        }
        $rooms = Room::where('hotel_id', $hotel)->whereNotIn('id', $data)->get();
        //$rooms = Room::where('hotel_id', $hotel)->get();

        return view('auth.accommodations.form', compact('rooms', 'hotel'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccommodationRequest $request)
    {
        $params = $request->all();
        Accommodation::create($params);
        //Mail::to('info@timmedia.store')->send(new RoomCreateMail($request));
        session()->flash('success', 'child ' . $request->title . ' created');
        return redirect()->route('accommodations.index');
    }

    /**
     * Display the specified resource.
     */
    public function edit(Request $request, Accommodation $child)
    {
        $hotel = $request->session()->get('hotel_id');
        $childs = Accommodation::where('hotel_id', $hotel)->get();
        foreach ($childs as $child) {
            $data[] = $child->room_id;
        }
        $rooms = Room::where('hotel_id', $hotel)->whereNotIn('id', $data)->get();

        return view('auth.accommodations.form', compact('child', 'rooms', 'hotel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccommodationRequest $request, Accommodation $child)
    {
        $params = $request->all();
        $child->update($params);
        //Mail::to('info@timmedia.store')->send(new RoomUpdateMail($request));
        session()->flash('success', 'Accommodation ' . $request->title . ' updated');
        return redirect()->route('accommodations.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Accommodation $child)
    {
        $child->delete();
        //Mail::to('info@timmedia.store')->send(new RoomDeleteMail($room));
        session()->flash('success', 'Accommodation ' . $child->title . ' deleted');
        return redirect()->route('accommodations.index');
    }
}
