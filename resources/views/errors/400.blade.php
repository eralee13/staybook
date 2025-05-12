@extends('layouts.head')

@section('title', 'Ошибка ' . $response->status())

@section('content')

    <div class="page about">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>Ошибка {{ $response->status() }}</h1>
                    <h4>Отправлен неверный запрос к серверу</h4>
                    <h4>Запрос завершился ошибкой: {{ $response->status() }}</h4>
                    <div class="btn-wrap">
                        <a href="{{ route('index') }}">Попробуйте снова</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


<style>
    .page{
        padding: 300px 0;
    }
    .page-not .text-wrap{
        text-align: center;
    }
    .page-not h4{
        margin: 20px 0 40px
    }
    .page-not a{
        color: #0163b4;
        text-decoration: none;
    }
</style>
