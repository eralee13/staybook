@extends('layouts.master')

@section('title', 'Поиск')

@section('content')

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    @livewire('search-hotel')
                </div>
            </div>
        </div>
    </div>

@endsection

