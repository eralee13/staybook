<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MealRequest;
use App\Models\Rate;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MealController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-meal|edit-meal|delete-meal', ['only' => ['index','show']]);
        $this->middleware('permission:create-meal', ['only' => ['create','store']]);
        $this->middleware('permission:edit-meal', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-meal', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $meals = Meal::where('hotel_id', $hotel)->paginate(20);
        return view('auth.meals.index', compact('meals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.meals.form', compact('hotel'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MealRequest $request)
    {
        $params = $request->all();
        Meal::create($params);
        //Mail::to('info@timmedia.store')->send(new RoomCreateMail($request));
        session()->flash('success', 'Meal ' . $request->title . ' created');
        return redirect()->route('meals.index');
    }

    /**
     * Display the specified resource.
     */
    public function edit(Request $request, Meal $meal)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.meals.form', compact('meal', 'hotel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MealRequest $request, Meal $food)
    {
        $params = $request->all();
        $food->update($params);
        //Mail::to('info@timmedia.store')->send(new RoomUpdateMail($request));
        session()->flash('success', 'Meal ' . $request->title . ' updated');
        return redirect()->route('meals.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Meal $meal)
    {
        $meal->delete();
        Rate::where('food_id', $meal->id)->update(['food_id' => null]);
        //Mail::to('info@timmedia.store')->send(new RoomDeleteMail($room));
        session()->flash('success', 'Meal ' . $meal->title . ' deleted');
        return redirect()->route('meals.index');
    }
}
