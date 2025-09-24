@extends('layouts.master')

@section('title', $page->title)

@section('content')

    <div class="page-about">
        <div class="page">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>{{ $page->__('title') }}</h1>
                        {!! $page->__('description') !!}
                    </div>
                </div>
            </div>
        </div>

        @if(app()->getLocale() == 'ru')
            <div class="callback">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="wrap">
                                <h3>Хотите сотрудничать?</h3>
                                <p>Пишите на <a href="mailto:contract@silkwaytravel.kg">contract@silkwaytravel.kg</a>
                                    или
                                    звоните: <a href="tel:+996 227 225 227">+996 227 225 227</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="callback">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="wrap">
                                <h3>Interested in partnership?</h3>
                                <p>Email us at <a href="mailto:contract@silkwaytravel.kg">{{ $contacts->email }}</a> or
                                    call: <a href="tel:{{ $contacts->phone }}">{{ $contacts->phone }}</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection
