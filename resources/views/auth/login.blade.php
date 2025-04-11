@extends('layouts.head')

@section('title', 'Логин')

@section('content')

    <div class="page order login">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-md-12">
                    <div class="login-wrap">
                        <h4>Войдите или <a href="{{ route('register') }}">зарегистрируйтесь</a></h4>
                        <div class="line"></div>
                        <h5>Добро пожаловать в StayBook</h5>
                        <form action="{{ route('login') }}" method="post">
                            <div class="form-group">
                                @error ('email')
                                <div class="alert alert-danger">email</div>
                                @enderror
                                <div class="label">Email</div>
                                <input type="email" name="email" value="{{ old('email', isset($user) ? $user->email :
                             null) }}" required>
                            </div>
                            <div class="form-group">
                                @error ('password')
                                <div class="alert alert-danger">password</div>
                                @enderror
                                <div class="label">Пароль</div>
                                <input type="password" name="password" id="password" autocomplete="current-password"
                                       value="{{ old('password', isset($user) ? $user->password : null) }}">
                                <div class="checkbox">
                                    <input type="checkbox" id="checkbox"><label for="checkbox">Показать пароль</label>
                                </div>
                                <script src="https://code.jquery.com/jquery-3.7.1.min.js"
                                        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
                                        crossorigin="anonymous"></script>
                                <script>
                                    $(document).ready(function () {
                                        $('#checkbox').on('change', function () {
                                            $('#password').attr('type', $('#checkbox').prop('checked') == true ? "text" : "password");
                                        });
                                    });
                                </script>

                                <style>
                                    .checkbox {
                                        margin-top: 10px;
                                    }

                                    .checkbox label {
                                        display: inline-block;
                                    }
                                </style>
                            </div>
                            <div class="line"></div>
                            <div class="descr">Нажимая кнопку ниже, вы соглашаетесь с <a href="#">политикой
                                    конфиденциальности</a> и с обработкой данных
                            </div>
                            @csrf
                            <div class="btn-wrap">
                                <button class="more">Войти в систему</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .order .form-group input[type="checkbox" i]{
            display: inline-block;
            width: auto;
            height: auto;
            margin-right: 10px;
        }
        .order .form-group input{
            border: 1px solid #f5f5f5;
            height: 46px;
            padding: 5px 10px;
        }
    </style>


@endsection
