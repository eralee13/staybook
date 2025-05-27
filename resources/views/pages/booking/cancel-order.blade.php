@extends('layouts.master')

@section('title', 'Отмена бронирования')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.book')</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>@lang('main.book')</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <table>
                        <tr>
                            <td>ID</td>
                            <td>Name</td>
                            <td>Sum</td>
                            <td>Action</td>
                        </tr>
                        @foreach($books as $book)
                            <tr>
                                <td>{{ $book->id }}</td>
                                <td>{{ $book->title }}</td>
                                <td>{{ $book->sum }}</td>
                                <td>
                                    <form action="{{ route('cancelBook') }}">
                                        @csrf
                                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                                        <input type="hidden" name="book_token" value="{{ $book->book_token }}">
                                        <button class="more">Отменить</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
