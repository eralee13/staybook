@extends('layouts.master')

@section('title', 'Поиск')

@section('content')

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ul>
                        @foreach($books as $book)
                            <li>{{ $book->title }}</li>
                        @endforeach
                    </ul>
                    @livewire('search-hotel')

                </div>
            </div>
        </div>
    </div>

@endsection

