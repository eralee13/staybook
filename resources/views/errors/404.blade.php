@extends('layouts.head')

@section('title', 'Ошибка 404')

@section('content')

<div class="page page-not">
    <div class="container">
        <div class="col-md-12">
            <div class="text-wrap">
                <h1>@lang('main.error') 404</h1>
                <div class="btn-wrap">
                    <a href="{{ route('index') }}">@lang('main.please_try_again')</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<style>
    .page{
        padding: 150px 0;
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