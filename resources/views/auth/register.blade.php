@extends('auth/layouts.master')

@section('title', 'Регистрация')

@section('content')

    <div class="page register">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-md-12">
                    <h3>Регистрация</h3>
                    <form method="POST" action="{{ route('register') }}">
                        @error ('name')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="form-group">
                            <label for="">ФИО</label>
                            <input type="text" name="name" value="{{ old('name', isset($order) ? $order->name :
                             null) }}">
                        </div>
                        @error ('email')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="form-group">
                            <label for="">Email</label>
                            <input type="email" name="email" value="{{ old('email', isset($order) ? $order->email :
                             null) }}">
                        </div>
                        @error ('password')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="form-group">
                            <label for="">Пароль</label>
                            <input type="password" name="password" value="{{ old('password', isset($order) ?
                            $order->password : null) }}">
                        </div>
                        @error ('password_confirmation')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="form-group">
                            <label for="">Подтверждение пароля</label>
                            <input type="password" name="password_confirmation" value="{{ old('password_confirmation', isset($order) ? $order->password_confirmation :
                             null) }}">
                        </div>
                        <div class="form-group">
                            <a href="{{ route('login') }}">
                                Уже зарегистрированы?
                            </a>
                        </div>
                        @csrf
                        <button class="more">Войти</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
