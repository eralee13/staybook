@extends('auth.layouts.master')

@section('title', $hotel->__('title'))

@section('content')

    <style>
        .admin .images img {
            max-width: 100%;
            height: 20vh;
            object-fit: cover;
            width: 100%;
            margin-bottom: 20px;
        }
    </style>
    @php
        $role = \Spatie\Permission\Models\Role::where('id', 3)->first();
    @endphp
    @if($role->name=='Hotel')
        <div class="page dashboard">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        @include('auth.layouts.sidebar')
                    </div>
                    <div class="col-md-6 main">
                        <div class="row">
                            <div class="col-md-11">
                                <h1>@lang('admin.main')</h1>
                            </div>
                            <div class="col-md-1">
                                <div class="btn-wrap">
                                    <form action="{{ route('hotels.edit', $hotel) }}">
                                        <button><img src="{{ route('index') }}/img/icons/edit.svg" alt=""></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @if($hotel->type)
                                <div class="col-md-3">
                                    <div class="dashboard-item">
                                        <div class="name">@lang('admin.property_type')</div>
                                        <h5>{{ $hotel->type }}</h5>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.title')</div>
                                    <h5>{{ $hotel->__('title') }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.timezone')</div>
                                    <h5>{{ $hotel->timezone ?? '+06:00' }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.city')</div>
                                    <h5>{{ $hotel->city }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.address')</div>
                                    <div class="address">{{ $hotel->__('address') }}</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">ID</div>
                                    <h5>{{ $hotel->id }}</h5>
                                </div>
                            </div>
                            @if($hotel->currency)
                                <div class="col-md-3">
                                    <div class="dashboard-item">
                                        <div class="name">@lang('main.currency')</div>
                                        <h5>{{ $hotel->currency }}</h5>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.checkin')</div>
                                    <h5>{{ $hotel->checkin }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.checkout')</div>
                                    <h5>{{ $hotel->checkout }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.rating')</div>
                                    @if($hotel->rating == 4)
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                    @elseif($hotel->rating == 5)
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                    @else
                                        @lang('admin.norating')
                                    @endif
                                </div>
                            </div>
                        </div>
                        @isset($hotel->amenity->services)
                            <div class="row" style="margin-top: 30px">
                                <div class="col-md-12">
                                    <div class="dashboard-item">
                                        <div class="name">@lang('admin.amenities')</div>
                                        <h6>{{ $hotel->amenity->services ?? '' }}</h6>
                                    </div>
                                </div>
                            </div>
                        @endisset
                        <div class="row">
                            <div class="col-md-12">
                                @if($hotel->description)
                                    <div class="dashboard-item">
                                        <div class="name">@lang('admin.description')</div>
                                        <div class="descr">{!! $hotel->__('description') !!}</div>
                                    </div>
                                @endif
                                <div class="dashboard-item">
                                    <div class="images">
                                        <div class="row">
                                            <div class="col-md-3">
                                                @if($hotel->image)
                                                    <img loading="lazy" src="{{ Storage::url($hotel->image) }}" alt="">
                                                @else
                                                    <img src="{{ route('index') }}/img/noimage.png" alt="">
                                                @endif
                                            </div>
                                            <div class="img-wrap">
                                                <div class="row">
                                                    @isset($images)
                                                        @foreach($images as $image)
                                                            <div class="col-md-3">
                                                                <div class="img-item">
                                                                    <img loading="lazy"
                                                                         src="{{ Storage::url($image->image) }}"
                                                                         alt="">
                                                                    <form action="{{ route('images.destroy', $image) }}"
                                                                          method="post">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button class="btn delete"
                                                                                onclick="return confirm('Do you want to delete this?');">
                                                                            <i class="fa-regular
                                                    fa-trash"></i></button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endisset
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="dashboard-item">
                                    <h3>@lang('admin.information')</h3>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="name">@lang('admin.phone_number')</div>
                                            <h5>{{ $hotel->phone }}</h5>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="name">Email</div>
                                            <h5>{{ $hotel->email }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 justify-content-between">
        
                                <!-- Кнопка для открытия модального окна -->
                        <button type="button" class="btn more add pull-right" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="fa-solid fa-plus"></i> &nbsp; @lang('admin.add')
                        </button>
                        <br><br><br>
                        
                        <div class="profile">
                            {{-- <div class="row">
                                <div class="col-md-8">
                                    <h3>@lang('admin.employess')</h3>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('users.create') }}"><i class="fa-regular fa-plus"></i></a>
                                </div>
                            </div> --}}
                            <h6>{{$users->name}} {{$users->lastname}}</h6>
                            <p><a href="{{ route('users.listHotel')}}">Master</a></p>
                            <div class="email"><i class="fa-regular fa-envelope"></i> {{ $users->email }}</div>
                            <br>
                            <div class="phone"><i class="fa-regular fa-phone"></i> {{ $users->phone }}</div>
                            

                            <div class="wrap">
                                {{-- @foreach($hotelUsers as $user)
                                    <a href="{{ route('users.show', $user->id) }}"><i class="fa-regular fa-pen-to-square"></i></a>
                                    <div class="name">{{ $user->name }}</div>
                                    <div class="position"> --}}
                                        {{-- @forelse ($user->getRoleNames() as $role)
                                            <span class="badge bg-primary">{{ $role }}</span>
                                        @empty
                                        @endforelse --}}
                                    {{-- </div>
                                    <div class="phone"><i class="fa-regular fa-phone"></i> {{ $user->phone }}</div>
                                    <div class="email"><i class="fa-regular fa-envelope"></i> {{ $user->email }}</div>
                                @endforeach --}}
                            </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <style>
            .modal {
                display: none;
                position: fixed;
                z-index: 1050;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: hidden;
                outline: 0;
                background: rgba(0,0,0,0.5);
                overflow-y: scroll;
            }

            .modal.show {
                display: block;
            }

            .modal-dialog {
                position: relative;
                width: auto;
                margin: 1.75rem auto;
                max-width: 800px;
            }

            .modal-content {
                position: relative;
                display: flex;
                flex-direction: column;
                background-color: #fff;
                background-clip: padding-box;
                border: 1px solid rgba(0,0,0,.2);
                border-radius: .3rem;
                outline: 0;
                padding: 1rem;
            }

            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 1rem;
            }

            .modal-header {
                border-bottom: 1px solid #dee2e6;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .modal-footer {
                border-top: 1px solid #dee2e6;
                display: flex;
                justify-content: flex-end;
                gap: .5rem;
            }

            .btn {
                display: inline-block;
                font-weight: 400;
                color: #212529;
                text-align: center;
                vertical-align: middle;
                user-select: none;
                background-color: #0d6efd;
                border: 1px solid transparent;
                padding: .375rem .75rem;
                font-size: 1rem;
                line-height: 1.5;
                border-radius: .25rem;
                color: #fff;
                cursor: pointer;
                text-decoration: none;
            }

            .btn-secondary {
                background-color: #6c757d;
            }

            .btn-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                line-height: 1;
                cursor: pointer;
            }

            .form-control {
                display: block;
                width: 100%;
                padding: .375rem .75rem;
                font-size: 1rem;
                line-height: 1.5;
                color: #212529;
                background-color: #fff;
                background-clip: padding-box;
                border: 1px solid #ced4da;
                border-radius: .25rem;
            }

            .form-label {
                margin-bottom: .5rem;
                display: inline-block;
            }
            .add{
                background-color: #74bb39 !important; 
            }
            .text-pass{
                font-size: 12px;
                padding: 10px;
                background-color: antiquewhite;
                display: block;
                border-radius: 7px;
                border: 1px solid #d6c9b8;
            }
        </style>

        <!-- Модальное окно -->
                <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel"   aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="createUserForm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createUserModalLabel">@lang('main.add_user')</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"><i class="fa-solid fa-close"></i></button>
                            </div>
                            <div class="modal-body">
                                @csrf
                                <div class="row">
                                    <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
                                    <div class="mb-3 col-md-6">
                                        <label for="name" class="form-label">@lang('main.fio')</label>
                                        <input type="text" class="form-control" id="name" name="name" required >
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="email" class="form-label">E-mail</label>
                                        <input type="email" class="form-control" id="email" name="email" required >
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="email" class="form-label">@lang('main.phone')</label>
                                        <input type="tel" name="phone" class="phone" id="phone">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        @php
                                            $allowed = ["Hotel Accountent", "Hotel Manager"];

                                            $filteredRoles = array_filter($roles, function ($role) use ($allowed) {
                                                return in_array($role, $allowed);
                                            });
                                        @endphp
                                        <label for="email" class="form-label">@lang('main.role')</label>
                                        <select class="form-select" id="roles" name="roles" required>
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
                                    <div class="mb-3 mt-3 col-md-12">
                                        <small class="form-text text-pass">
                                            @lang('main.password_help')
                                        </small>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <div class="input-group">
                                            <label for="password" class="form-label">@lang('main.password')</label>
                                            <input type="password" class="form-control password-field" id="password" name="password" required>
                                        </div>

                                        <div id="passwordHelp" class="form-text text-danger d-none">@lang('main.password_not_regex')</div>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        
                                            <div class="input-group">
                                                <label for="password" class="form-label">@lang('main.password_confirmation')</label>
                                                <input type="password" id="password_confirmation" name="password_confirmation" class="password-field" required>
                                            </div>

                                        <div id="confirmHelp" class="form-text text-danger d-none">@lang('main.password_confirm')</div>
                                    </div>
                                    <div class="mb3 col-md-12 d-flex justify-content-end" style="gap: 20px">
                                        <button type="button" class="btn more" onclick="generatePassword()">@lang("main.generate_password")</button>
                                        <button type="button" class="btn more" id="togglePasswordVisibility">
                                                    👁️ @lang('main.show_password')
                                                </button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn cancel" data-bs-dismiss="modal">@lang('main.cancel')</button>
                                <button type="submit" class="btn more add">@lang('main.create')</button>
                            </div>
                        </div>
                        </form>
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
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('createUserForm');

                form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);

                fetch('{{ route('users.createHotelUsers') }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: formData
                    })
                    .then(data => {
                        console.log(data);
                        if (data.status === 409) {
                            alert('Пользователь с таким email уже существует!');
                            return; // дальше не продолжаем
                        }

                        if(data.status === 200) {
                            alert('Пользователь успешно создан!');
                            document.getElementById('createUserModal').classList.remove('show');
                            // form.reset();
                            location.reload();
                        } 

                        if(data.status === 500) {
                            alert('У вас нет прав для создания пользователя, с таким уровнем доступа.');
                        }
                        
                    })
                    .catch(error => {
                        console.error(error);
                        alert('Упс, что-то пошло не так. Пожалуйста, попробуйте еще раз.');
                    });

                });
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
        <script>
            // Открыть модалку
            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                const target = btn.getAttribute('data-bs-target');
                document.querySelector(target).classList.add('show');
                });
            });

            // Закрыть модалку
            document.querySelectorAll('.btn-close, [data-bs-dismiss="modal"]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                btn.closest('.modal').classList.remove('show');
                });
            });

            // Закрыть модалку по клику на фон
            document.querySelectorAll('.modal').forEach(function(modal) {
                modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
                });
            });
        </script>

    @else
        <div class="page admin dashboard">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        @include('auth.layouts.sidebar')
                    </div>
                    <div class="col-md-6 main">
                        @if(session()->has('success'))
                            <p class="alert alert-success">{{ session()->get('success') }}</p>
                        @endif
                        @if(session()->has('warning'))
                            <p class="alert alert-warning">{{ session()->get('warning') }}</p>
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <h1>@lang('admin.main')</h1>
                            </div>
                            <div class="col-md-6">
                                <div class="btn-wrap">
                                    <form action="{{ route('hotels.edit', $hotel) }}">
                                        <button><i class="fa-regular fa-pen-to-square"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.title')</div>
                                    <h5>{{ $hotel->__('title') }}</h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                {{--                            <div class="dashboard-item">--}}
                                {{--                                <div class="name">Часовой пояс</div>--}}
                                {{--                                <h5>+06 (UTC +6)--}}
                                {{--                                </h5>--}}
                                {{--                            </div>--}}
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.address')</div>
                                    <div class="address">{{ $hotel->__('address') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">ID</div>
                                    <h5>{{ $hotel->id }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.number_of_rooms')</div>
                                    <h5>{{ $hotel->count }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.checkin')</div>
                                    <h5>{{ $hotel->checkin }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.checkout')</div>
                                    <h5>{{ $hotel->checkout }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.early_checkin')</div>
                                    <h5>{{ $hotel->early_in }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.late_checkout')</div>
                                    <h5>{{ $hotel->early_out }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.rating')</div>
                                    @if($hotel->rating == 2)
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                    @elseif($hotel->rating == 3)
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                    @elseif($hotel->rating == 4)
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                    @else
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                        <i class="fa-regular fa-star"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="dashboard-item">
                                    <div class="name">@lang('admin.description')</div>
                                    <div class="descr">{!! $hotel->__('description') !!}</div>
                                </div>
                                <div class="dashboard-item">
                                    <div class="images">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <img loading="lazy" src="{{ Storage::url($hotel->image) }}" alt="">
                                            </div>
                                            @isset($images)
                                                @foreach($images as $image)
                                                    <div class="col-md-3">
                                                        <img loading="lazy" src="{{ Storage::url($image->image) }}"
                                                             alt="">
                                                    </div>
                                                @endforeach
                                            @endisset
                                        </div>
                                    </div>
                                </div>
                                <div class="dashboard-item">
                                    <h3>@lang('admin.information')</h3>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="name">@lang('admin.phone_number')</div>
                                            <h5>{{ $hotel->phone }}</h5>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="name">Email</div>
                                            <h5>{{ $hotel->email }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="profile">
                            <div class="row">
                                <div class="col-md-8">
                                    <h3>@lang('admin.employess')</h3>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('users.create') }}"><i class="fa-regular fa-plus"></i></a>
                                </div>
                            </div>
                            <div class="wrap">
                                <a href="{{ route('profile.edit') }}"><i class="fa-regular fa-pen-to-square"></i></a>
                                <div class="name">{{ $users->name }}</div>
                                <div class="position">
                                    @forelse ($users->getRoleNames() as $role)
                                        <span class="badge bg-primary">{{ $role }}</span>
                                    @empty
                                    @endforelse
                                </div>
                                <div class="phone"><i class="fa-regular fa-phone"></i> {{ $users->phone }}</div>
                                <div class="email"><i class="fa-regular fa-envelope"></i> {{ $users->email }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
