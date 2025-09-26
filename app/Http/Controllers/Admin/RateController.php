<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RateRequest;
use App\Models\CancellationRule;
use App\Models\Meal;
use App\Models\Rate;
use App\Models\Room;
use App\Models\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-rate|edit-rate|delete-rate', ['only' => ['index','show']]);
        $this->middleware('permission:create-rate', ['only' => ['create','store']]);
        $this->middleware('permission:edit-rate', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-rate', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $rates = Rate::where('hotel_id', $hotel)->paginate(10);
        return view('auth.rates.index', compact('hotel', 'rates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $rooms = Room::where('hotel_id', $hotel)->get();
        $meals = Meal::all();
        $cancellations = CancellationRule::where('hotel_id', $hotel)->get();
        return view('auth.rates.form', compact('rooms', 'hotel', 'meals', 'cancellations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RateRequest $request)
    {
        $params = $request->all();
        Rate::create($params);
        session()->flash('success', 'Rate ' . $request->title . ' created');
        return redirect()->route('rates.index');
    }

    /**
     * Display the specified resource.
     */

    public function edit(Request $request, Rate $rate)
    {
        $hotel = $request->session()->get('hotel_id');
        $meals = Meal::all();
        $cancellations = CancellationRule::where('hotel_id', $hotel)->get();
        $rooms = Room::where('hotel_id', $hotel)->get();
        return view('auth.rates.form', compact('rate', 'hotel', 'meals', 'cancellations', 'rooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RateRequest $request, Rate $rate)
    {
        $params = $request->all();
        $rate->update($params);
        //Mail::to('info@timmedia.store')->send(new RoomUpdateMail($request));
        session()->flash('success', 'Rate ' . $request->title . ' updated');
        return redirect()->route('rates.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rate $rate)
    {
        $rate->delete();
        //Mail::to('info@timmedia.store')->send(new RoomDeleteMail($room));
        session()->flash('success', 'Rate ' . $rate->title . ' deleted');
        return redirect()->route('rates.index');
    }

}
