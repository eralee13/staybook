@extends('auth/layouts.master')

@section('title', 'Забыли пароль?')

@section('content')

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-md-12">
                    <h3>Восстановление пароля</h3>
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="form-group">
                            <label for="">Email</label>
                            <input type="email" name="email">
                        </div>
                        <button class="more">Отправить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection
