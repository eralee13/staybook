@extends('layouts.master')

@section('title', 'Ошибка 419')

@section('content')

    <style>
        .page {
            padding: 90px 0 200px;
        }

        .btn-wrap {
            margin-top: 40px;
        }
    </style>

    <div class="page page-not">
        <div class="container">
            <div class="col-md-12">
                <div class="text-wrap">
                    <h1>Сеанс истёк</h1>
                    <p>Похоже, вы слишком долго заполняли форму или вкладка простаивала. Обновите страницу и попробуйте
                        ещё раз.</p>
                    <div class="btn-wrap">
                        <a href="{{ route('index') }}">@lang('main.please_try_again')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
