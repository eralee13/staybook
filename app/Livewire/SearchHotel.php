<?php

namespace App\Livewire;

use App\Models\Hotel;
use Livewire\Component;

class SearchHotel extends Component
{

    public $search = '';
    public function render()
    {
        $hotels = Hotel::where('title', 'like', '%' . $this->search . '%')
        ->orWhere('description', 'like', '%' . $this->search . '%')
        ->get();
        return view('livewire.search-hotel', compact('hotels'));
    }
}
