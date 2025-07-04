<?php


namespace App\Livewire;

use Livewire\Component;

class LWTester extends Component
{
    public $selectedOption = '1'; // значение по умолчанию

    public function render()
    {
        return view('livewire.lwtester')->extends('layouts.master');
    }
}