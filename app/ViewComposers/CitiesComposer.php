<?php

namespace App\ViewComposers;

use App\Models\City;
use Illuminate\View\View;

class CitiesComposer
{
    public function compose(View $view){
        $cities = City::get();
        $view->with('cites', $cities);
    }
}