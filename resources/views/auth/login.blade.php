@extends('layouts.master')

@section('title', 'Логин')

@section('content')

    <style>
        .login a {
            color: #78c252;
            text-decoration: none;
        }

        :root {
            --cb-size: 30px; /* размер квадрата чекбокса */
            --cb-radius: 5px; /* скругление */
            --cb-border: 2px;
            --accent: #54b25a; /* зелёный чек */
            --text: #0b0b0b;
            --muted: #8b8b8b;
            --surface: #ffffff;
            --shadow: 0 1px 2px rgba(0, 0, 0, .04), 0 6px 24px rgba(0, 0, 0, .06);
            --focus: 0 0 0 3px rgba(84, 178, 90, .25);
            font-synthesis-weight: none;
        }

        /* Базовая разметка опции */
        .option {
            display: grid;
            grid-template-columns: var(--cb-size) 1fr;
            align-items: center;
            gap: 18px;
            font-size: 16px;
            line-height: 1.1;
            cursor: pointer;
            user-select: none;
        }

        /* Скрываем нативный чекбокс, оставляя доступность */
        .option input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* Визуальная коробка чекбокса */
        .box {
            width: var(--cb-size);
            height: var(--cb-size);
            border-radius: var(--cb-radius);
            background: var(--surface);
            border: var(--cb-border) solid #e6e6e6;
            display: inline-block;
            place-items: center;
            box-shadow: var(--shadow);
            transition: border-color .18s ease, transform .12s ease, background .18s ease;
        }

        /* Галочка (SVG) скрыта по умолчанию */
        .box svg {
            width: 100%;
            height: 100%;
            display: none;
            position: relative;
            left: 2px;
        }

        .admin .name {
            display: inline-block;
            position: relative;
            top: -9px;
            margin-left: 10px;
            opacity: 1;
        }

        /* Hover для всей зоны клика */
        .option:hover .box {
            transform: translateY(-1px);
        }

        /* Состояние :checked */
        .option input:checked + .box {
            border-color: transparent;
            background: var(--surface);
        }

        .option input:checked + .box svg {
            display: block;
        }

        .option input:checked + .box svg path {
            fill: var(--accent);
        }

        /* Фокус-контур для доступности (по Tab) */
        .option input:focus-visible + .box {
            box-shadow: var(--focus), var(--shadow);
        }

        /* Неактивное/заблокированное при желании */
        .option[aria-disabled="true"] {
            opacity: .5;
            pointer-events: none;
        }

        .login form .form-group.check input {
            display: none;
        }

        .option .name {
            position: relative;
            top: -8px;
            left: 5px;
        }

    </style>

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
                             null) }}" required placeholder="email">
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" id="password" autocomplete="current-password"
                                           value="{{ old('password', isset($user) ? $user->password : null) }}"
                                           placeholder="@lang('main.password')">
                                </div>
                                <div class="form-group check">
                                    <label for="checkbox" class="option">
                                        <input type="checkbox" id="checkbox">
                                        <span class="box" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" role="presentation"
                                                                 focusable="false">
                                                              <path d="M9.2 17.6c-.4 0-.8-.2-1.1-.5l-3.9-4a1.6 1.6 0 1 1 2.2-2.2l2.8 2.9 6.6-7a1.6 1.6 0 1 1 2.4 2.1l-7.7 8.2c-.3.3-.7.5-1.3.5z"/>
                                                            </svg>
                                                          </span>
                                        <span class="name">@lang('main.show_password')</span>
                                    </label>
                                </div>
                                <div class="line"></div>
                                <div class="form-group">
                                    <div class="descr">@lang('main.agree_with') <a
                                                href="{{ route('privacy') }}">@lang('main.privacy_policy')</a> @lang('main.processing_data')
                                    </div>
                                </div>
                                <div class="form-group check">
                                    <label for="" class="option">
                                        <input type="checkbox">
                                        <span class="box" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" role="presentation"
                                                                 focusable="false">
                                                              <path d="M9.2 17.6c-.4 0-.8-.2-1.1-.5l-3.9-4a1.6 1.6 0 1 1 2.2-2.2l2.8 2.9 6.6-7a1.6 1.6 0 1 1 2.4 2.1l-7.7 8.2c-.3.3-.7.5-1.3.5z"/>
                                                            </svg>
                                                          </span>
                                        <span class="name">@lang('main.remember')</span>
                                    </label>
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