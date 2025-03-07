<?php

namespace App\ViewComposers;

use App\Models\Room;
use Illuminate\View\View;

class RoomsComposer
{
    public function compose(View $view){
        $rooms = Room::get();
        $view->with('rooms', $rooms);
    }
}