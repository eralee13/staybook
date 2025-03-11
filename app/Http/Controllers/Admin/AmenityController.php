<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;


class AmenityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-amenity|edit-amenity|delete-amenity', ['only' => ['index','show']]);
        $this->middleware('permission:create-amenity', ['only' => ['create','store']]);
        $this->middleware('permission:edit-amenity', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-amenity', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $amenities = Amenity::where('hotel_id', $hotel)->get();

        return view('auth.amenities.index', compact('amenities', 'hotel'));
    }

    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.amenities.form', compact('hotel'));
    }
    public function store(Request $request)
    {
        $params = [
            'title' => $request->title,
            'hotel_id' => $request->hotel_id,
            'amenities' => implode(', ', $request->services),
        ];
        Amenity::create($params);

        session()->flash('success', 'Amenity created successfully');
        return redirect()->route('amenities.index');
    }
    public function edit(Request $request, Amenity $amenity)
    {
        $hotel = $request->session()->get('hotel_id');
        $amenities = explode(', ', $amenity->services);
        return view('auth.amenities.form', compact('amenity', 'hotel', 'amenities'));
    }

    public function update(Request $request, Amenity $amenity)
    {
        $params = [
            'title' => $request->title,
            'hotel_id' => $request->hotel_id,
            'services' => implode(', ', $request->services),
        ];
        //$params = $request->all();
        $amenity->update($params);
        session()->flash('success', 'Amenities updated successfully');

        return redirect()->route('amenities.index');
    }

}
