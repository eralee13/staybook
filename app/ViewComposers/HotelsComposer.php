<?php

namespace App\ViewComposers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HotelsComposer
{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function compose(View $view){
        if(Auth::user() == null){
            $hotels = Hotel::all();
            $view->with('hotels', $hotels);
        } else{
            $user = Auth::user()->id;
            if($user != 1 && $user != 3){
                $hotels = Hotel::where('user_id', $user)->paginate(10);
                $hotel_id = $this->request->session()->get('hotel_id');
                $view->with('hotels', $hotels)->with('hotel_id', $hotel_id);
            } else{
                $hotels = Hotel::all();
                $hotel_id = $this->request->session()->get('hotel_id');
                $view->with('hotels', $hotels)->with('hotel_id', $hotel_id);
            }

        }



    }
}