<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\RoomUpdateMail;
use App\Models\CategoryRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CategoryRoomController extends Controller
{
    public function index()
    {
        $cats = CategoryRoom::paginate(20);
        return view('auth.categoryRooms.index', compact('cats'));
    }

    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.categoryRooms.form', compact('hotel'));
    }

    public function store(Request $request){
        $request['code'] = Str::slug($request->title);
        $params = $request->all();
        CategoryRoom::create($params);
        //Mail::to('info@timmedia.store')->send(new RoomCreateMail($request));
        session()->flash('success', 'Rate room ' . $request->title . ' created');
        return redirect()->route('categoryRooms.index');
    }

    public function edit(CategoryRoom $cat, Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.categoryRooms.form', compact('cat', 'hotel'));
    }

    public function update(Request $request, CategoryRoom $cat)
    {
        $request['code'] = Str::slug($request->title);
        $params = $request->all();
        $cat->update($params);
        //Mail::to('info@timmedia.store')->send(new RoomUpdateMail($request));
        session()->flash('success', 'Rate room ' . $cat->title . ' updated');
        return redirect()->route('categoryRooms.index');
    }

    public function destroy(CategoryRoom $cat)
    {
        $cat->delete();
        //Mail::to('info@timmedia.store')->send(new RoomUpdateMail($request));
        session()->flash('success', 'Rate room ' . $cat->title . ' updated');
        return redirect()->route('categoryRooms.index');
    }
}
