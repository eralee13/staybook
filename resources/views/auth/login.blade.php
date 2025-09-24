@extends('layouts.master')

@section('title', 'Логин')

@section('content')

    <div class="page login register">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12">
                    <div class="login-wrap">
                        <div class="wrap">
                            <h3>@lang('main.welcome')</h3>
                            <form action="{{ route('login') }}" method="post">
                                <div class="form-group">
                                    <input type="email" name="email" value="{{ old('email', isset($user) ? $user->email :
                             null) }}" required>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" id="password" autocomplete="current-password"
                                           value="{{ old('password', isset($user) ? $user->password : null) }}">
                                </div>
                                <div class="form-group check">
                                    <input type="checkbox" id="checkbox">
                                    <label for="checkbox">@lang('main.show_password')</label>
                                </div>
                                <div class="line"></div>
                                <div class="form-group">
                                    <div class="descr">@lang('main.agree_with') <a
                                                href="{{ route('privacy') }}">@lang('main.privacy_policy')</a> @lang('main.processing_data')
                                    </div>
                                </div>
                                <div class="form-group check">
                                    <input type="checkbox">
                                    <label for="">@lang('main.remember')</label>
                                </div>
                                <div class="form-group">
                                    @if (Route::has('password.request'))
                                        <a class="btn btn-link" href="{{ route('password.request') }}">
                                            {{ __('Забыли пароль?') }}
                                        </a>
                                    @endif
                                </div>
                                @csrf
                                <div class="form-group">
                                    <div class="btn-wrap">
                                        <button class="more">@lang('main.login_system')</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

@endsection