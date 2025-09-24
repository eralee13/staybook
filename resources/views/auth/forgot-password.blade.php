@extends('layouts.master')

@section('title', 'Забыли пароль?')

@section('content')

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="page" style="margin-bottom: 60px">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-md-12">
                    <h3>Восстановление пароля</h3>
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="form-group">
                            <label for="">Email</label>
                            <input type="email" name="email">
                            @error('email')
                            <div class="alert alert-danger">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <button class="more">Отправить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection
