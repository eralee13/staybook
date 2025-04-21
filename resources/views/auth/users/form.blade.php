@extends('auth.layouts.master')

@isset($user)
    @section('title', 'Редактировать пользователя' . $user->name)
@else
    @section('title', 'Создать пользователя')
@endisset

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @isset($user)
                        <h1>@lang('admin.edit_user') {{ $user->title }}</h1>
                    @else
                        <h1>@lang('admin.add_user')</h1>
                    @endisset
                    <form method="post"
                          @isset($user)
                              action="{{ route('users.update', $user) }}"
                          @else
                              action="{{ route('users.store') }}"
                            @endisset
                    >
                        @isset($user)
                            @method('PUT')
                        @endisset
                        <div class="row">
                            <div class="col-md-6">
                                @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                <div class="form-group">
                                    <label for="name">@lang('admin.name')</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', isset($user) ? $user->name :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @error('email')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', isset($user) ?
                                $user->email : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @error('phone')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                <div class="form-group">
                                    <label for="phone">@lang('admin.phone')</label>
                                    <input type="tel" name="phone" class="phone" id="phone" value="{{ old('phone', isset
                            ($user) ? $user->phone : null) }}">
                                    <div id="output" class="output"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">@lang('admin.role')</label>
                                    <select class="form-select @error('roles') is-invalid @enderror" aria-label="Roles"
                                            id="roles" name="roles[]">
                                        @forelse ($roles as $role)

                                            @if ($role!='Super Admin')
                                                <option value="{{ $role }}" {{ in_array($role, $userRoles ?? []) ? 'selected' : '' }}>
                                                    {{ $role }}
                                                </option>
                                            @else
                                                @if (Auth::user()->hasRole('Super Admin'))
                                                    <option value="{{ $role }}" {{ in_array($role, $userRoles ?? []) ? 'selected' : '' }}>
                                                        {{ $role }}
                                                    </option>
                                                @endif
                                            @endif
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Название банка</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">ИНН</label>
                                    <input type="text" name="bank_inn" value="{{ old('bank_inn', $user->bank_inn) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Р/с</label>
                                    <input type="text" name="bank_account" value="{{ old('bank_account', $user->bank_account) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">БИК</label>
                                    <input type="text" name="bank_bic" value="{{ old('bank_bic', $user->bank_bic) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Адрес</label>
                                    <input type="text" name="address" value="{{ old('address', $user->address) }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    @error('password')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <label for="">Пароль</label>
                                    <input type="password" id="password" name="password" value="{{ old('password', isset
                            ($user) ? $user->password : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @error('password_confirmation')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <label for="">Подтвердите пароль</label>
                                    <input type="password" id="password_confirm" name="password_confirmation" value="{{ old('password', isset
                            ($user) ? $user->password : null) }}">
                                </div>
                            </div>
                        </div>
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
                                        $('#password_confirm').attr('type', $('#checkbox').prop('checked') == true ? "text" : "password");
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
                        @csrf
                        <button class="more">@lang('admin.send')</button>
                        <a href="{{url()->previous()}}" class="btn delete cancel">@lang('admin.cancel')</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
