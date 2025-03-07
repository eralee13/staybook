@extends('auth/layouts.master')

@section('title', 'Логин')

@section('content')

    <div class="page admin login">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-md-12">
                    <h2>Авторизация</h2>
                    <form method="POST" action="{{ route('login') }}">
                        @error ('email')
                            <div class="alert alert-danger">email</div>
                        @enderror
                        <div class="form-group">
                            <label for="">Email</label>
                            <input type="email" name="email" value="{{ old('email', isset($user) ? $user->email :
                             null) }}">
                        </div>
                        @error ('password')
                            <div class="alert alert-danger">password</div>
                        @enderror
                        <div class="form-group">
                            <label for="">Пароль</label>
                            <input type="password" name="password" id="password" autocomplete="current-password"
                                   value="{{ old('password', isset($user) ? $user->password : null) }}">
                            <div class="checkbox">
                                <input type="checkbox" id="checkbox"><label for="checkbox">Показать пароль</label>
                            </div>
                            <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
                            <script>
                                $(document).ready(function(){
                                    $('#checkbox').on('change', function(){
                                        $('#password').attr('type',$('#checkbox').prop('checked')==true?"text":"password");
                                    });
                                });
                            </script>

                            <style>
                                .checkbox{
                                    margin-top: 10px;
                                }
                                .checkbox label{
                                    display: inline-block;
                                }
                            </style>

                        </div>
                        <!-- Remember Me -->
{{--                        <div class="form-group">--}}
{{--                            <label for="remember_me" class="inline-flex items-center">--}}
{{--                                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">--}}
{{--                                <span>Запомнить меня</span>--}}
{{--                            </label>--}}
{{--                        </div>--}}
                        <div class="form-group">
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">
                                    Забыли пароль?
                                </a>
                            @endif
                        </div>
                        @csrf
                        <button class="more">Войти</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
