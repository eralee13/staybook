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
                              action="{{ route('users.updateHotel', $user) }}"
                        @else
                              action="{{ route('users.createHotel') }}"
                        @endisset
                    >
                        @isset($user)
                            @method('PUT')
                        @endisset
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">@lang('admin.name')</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', isset($user) ? $user->name :
                             null) }}">
                                </div>
                                @error('name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', isset($user) ?
                                $user->email : null) }}">
                                </div>
                                @error('email')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
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
                                    @php
                                        $allowed = ["Hotel Accountent", "Hotel Manager"];

                                        $filteredRoles = array_filter($roles, function ($role) use ($allowed) {
                                            return in_array($role, $allowed);
                                        });
                                    @endphp
                                    <select class="form-select @error('roles') is-invalid @enderror" aria-label="Roles"
                                            id="roles" name="roles">
                                            
                                        @forelse ($filteredRoles as $role)

                                            @if ($role!='Super Admin')
                                                <option value="{{ $role }}" {{ in_array($role, $userRoles ?? []) ? 'selected' : '' }}>
                                                    {{ $role }}
                                                </option>
                                            @else
                                                @if (Auth::user()->hasRole('Hotel'))
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
                            {{-- <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Название банка</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name', isset
                            ($user) ? $user->bank_name : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">ИНН</label>
                                    <input type="text" name="bank_inn" value="{{ old('bank_inn', isset
                            ($user) ? $user->bank_inn : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Р/с</label>
                                    <input type="text" name="bank_account" value="{{ old('bank_account', isset
                            ($user) ? $user->bank_account : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">БИК</label>
                                    <input type="text" name="bank_bic" value="{{ old('bank_bic', isset
                            ($user) ? $user->bank_bic : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Адрес</label>
                                    <input type="text" name="address" value="{{ old('address', isset
                            ($user) ? $user->address : null) }}">
                                </div>
                            </div> --}}
                        </div>
                        <div class="row">
                            <div class="mb-3 mt-3 col-md-12">
                                <style>
                                    .text-pass{
                                        font-size: 12px;
                                        padding: 10px;
                                        background-color: antiquewhite;
                                        display: block;
                                        border-radius: 7px;
                                        border: 1px solid #d6c9b8;
                                    }
                                </style>
                                <small class="form-text text-pass">
                                    @lang('main.password_help')
                                </small>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Пароль</label>
                                    <input type="password" id="password" class="password-field" name="password" required
                                        @if( isset($user) )
                                            value="{{ old('password', isset($user) ? $user->password : null) }}"
                                        @endif
                                    >
                                    @error('password')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Подтвердите пароль</label>
                                    <input type="password" id="password_confirmation" class="password-field" name="password_confirmation" required
                                    @if( isset($user) )
                                        value="{{ old('password', isset($user) ? $user->password : null) }}"
                                    @endif
                                    >
                                    @error('password_confirmation')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb3 col-md-12 d-flex justify-content-end" style="gap: 20px">
                            <button type="button" class="btn more" onclick="generatePassword()">@lang("main.generate_password")</button>
                            <button type="button" class="btn more" id="togglePasswordVisibility">
                                👁️ @lang('main.show_password')
                            </button>
                        </div>
                        <input type="hidden" name="hotel_id" value="{{ $hotel }}">
                        @csrf
                        <button class="more">@lang('admin.send')</button>
                        <a href="{{ route('users.listHotel') }}" class="btn delete cancel">@lang('admin.cancel')</a>

                    </form>
                </div>
            </div>
        </div>
    </div>
        <script>
            document.getElementById('togglePasswordVisibility').addEventListener('click', function () {
                const fields = document.querySelectorAll('.password-field');
                let showing = false;
                let showingText = @json(__('main.hide_password')); 
                let hiddenText = @json(__('main.show_password')); 

                fields.forEach(field => {
                    if (field.type === 'password') {
                        field.type = 'text';
                        showing = true;
                    } else {
                        field.type = 'password';
                    }
                });

                this.textContent = showing ? '🙈 ' + showingText : '👁️ ' + hiddenText;
            });
        </script>
        <script>
            function generatePassword() {
                const lower = "abcdefghijklmnopqrstuvwxyz";
                const upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                const digits = "0123456789";
                const special = "!@#$%^&*()_+";
                const all = lower + upper + digits + special;

                // Гарантированно добавляем по 1 символу из каждой группы
                let password = 
                    lower.charAt(Math.floor(Math.random() * lower.length)) +
                    upper.charAt(Math.floor(Math.random() * upper.length)) +
                    digits.charAt(Math.floor(Math.random() * digits.length)) +
                    special.charAt(Math.floor(Math.random() * special.length));

                // Добавляем остальные символы случайно
                const remainingLength = 6;
                for (let i = 0; i < remainingLength; i++) {
                    password += all.charAt(Math.floor(Math.random() * all.length));
                }

                // Перемешиваем пароль (чтобы обязательные символы не были в начале)
                password = password.split('').sort(() => Math.random() - 0.5).join('');

                // Установка в поля формы
                document.getElementById("password").value = password;
                document.getElementById("password_confirmation").value = password;
            }


            // Регулярка: 6+ символов, минимум одна большая, маленькая, цифра и спецсимвол
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{6,}$/;

            document.getElementById('createUserForm').addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmation = document.getElementById('password_confirmation').value;
                const passHelp = document.getElementById('passwordHelp');
                const confirmHelp = document.getElementById('confirmHelp');

                let hasError = false;

                // Проверка соответствия регексу
                if (!passwordRegex.test(password)) {
                    passHelp.classList.remove('d-none');
                    hasError = true;
                } else {
                    passHelp.classList.add('d-none');
                }

                // Проверка совпадения паролей
                if (password !== confirmation) {
                    confirmHelp.classList.remove('d-none');
                    hasError = true;
                } else {
                    confirmHelp.classList.add('d-none');
                }

                if (hasError) {
                    e.preventDefault();
                }
            });
        </script>
@endsection
