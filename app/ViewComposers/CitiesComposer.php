<?php

namespace App\ViewComposers;

use App\Models\City;
use Illuminate\View\View;

class CitiesComposer
{
    public function compose(View $view)
    {
        $cities = cache()->remember('cities_all_v1', 3600, function () {
            return \App\Models\City::query()
                ->select(['id','title','country_id']) // только нужное
                ->orderBy('title')
                ->get()
                ->map(fn($c) => [
                    'id'         => $c->id,
                    'title'      => $c->title,
                    'title_en'   => $c->title_en,
                    'country_id' => $c->country_id,
                ])
                ->all(); // <-- обычный массив
        });

        $view->with('cities', $cities);
    }
}