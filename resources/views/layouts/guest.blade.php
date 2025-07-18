@extends('layouts.master')

@section('title', 'Сброс пароля')
@section('content')
<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
@endsection