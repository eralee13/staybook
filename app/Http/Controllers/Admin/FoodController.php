<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FoodRequest;
use App\Models\Category;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FoodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-food|edit-food|delete-food', ['only' => ['index','show']]);
        $this->middleware('permission:create-food', ['only' => ['create','store']]);
        $this->middleware('permission:edit-food', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-food', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $foods = Food::where('hotel_id', $hotel)->paginate(20);
        return view('auth.foods.index', compact('foods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.foods.form', compact('hotel'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FoodRequest $request)
    {
        $params = $request->all();
        Food::create($params);
        //Mail::to('info@timmedia.store')->send(new RoomCreateMail($request));
        session()->flash('success', 'Food ' . $request->title . ' created');
        return redirect()->route('foods.index');
    }

    /**
     * Display the specified resource.
     */
    public function edit(Request $request, Food $food)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.foods.form', compact('food', 'hotel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FoodRequest $request, Food $food)
    {
        $params = $request->all();
        $food->update($params);
        //Mail::to('info@timmedia.store')->send(new RoomUpdateMail($request));
        session()->flash('success', 'Food ' . $request->title . ' updated');
        return redirect()->route('foods.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Food $food)
    {
        $food->delete();
        Category::where('food_id', $food->id)->update(['food_id' => null]);
        //Mail::to('info@timmedia.store')->send(new RoomDeleteMail($room));
        session()->flash('success', 'Food ' . $food->title . ' deleted');
        return redirect()->route('foods.index');
    }
}
