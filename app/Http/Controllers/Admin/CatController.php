<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\RoomUpdateMail;
use App\Models\Cat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CatController extends Controller
{
    public function index()
    {
        $cats = Cat::paginate(20);
        return view('auth.cats.index', compact('cats'));
    }

    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.cats.form', compact('hotel'));
    }

    public function store(Request $request){
        $request['code'] = Str::slug($request->title);
        $params = $request->all();
        Cat::create($params);
        //Mail::to('info@timmedia.store')->send(new RoomCreateMail($request));
        session()->flash('success', 'Category room ' . $request->title . ' created');
        return redirect()->route('cats.index');
    }

    public function edit(Cat $cat, Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.cats.form', compact('cat', 'hotel'));
    }

    public function update(Request $request, Cat $cat)
    {
        $request['code'] = Str::slug($request->title);
        $params = $request->all();
        $cat->update($params);
        //Mail::to('info@timmedia.store')->send(new RoomUpdateMail($request));
        session()->flash('success', 'Category room ' . $cat->title . ' updated');
        return redirect()->route('cats.index');
    }

    public function destroy(Cat $cat)
    {
        $cat->delete();
        //Mail::to('info@timmedia.store')->send(new RoomUpdateMail($request));
        session()->flash('success', 'Category room ' . $cat->title . ' updated');
        return redirect()->route('cats.index');
    }
}
