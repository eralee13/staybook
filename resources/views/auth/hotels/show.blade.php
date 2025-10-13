@extends('auth.layouts.master')

@section('title', $hotel->__('title'))

@section('content')
    @php
        $role = \Spatie\Permission\Models\Role::where('id', 3)->first();
    @endphp
    @if($role->name=='Hotel')
        <div class="page dashboard">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        @include('auth.layouts.sidebar')
                    </div>
                    <div class="col-md-9 main">
                        <h1 style="margin-bottom: 16px">@lang('admin.main')</h1>
                        <div class="row">
                            <div class="col-md-9">
                                <div class="btn-wrap">
                                    <form action="{{ route('hotels.edit', $hotel) }}">
                                        <button class="more">@lang('admin.edit')</button>
                                    </form>
                                    <div class="table-wrap">
                                        <table>
                                            @if($hotel->type)
                                                <tr>
                                                    <td style="border-top: none">@lang('admin.property_type')</td>
                                                    <td style="border-top: none">{{ $hotel->type }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td>@lang('admin.title')</td>
                                                <td>{{ $hotel->__('title') }}</td>
                                            </tr>
                                            <tr>
                                                <td>@lang('admin.timezone')</td>
                                                <td>{{ $hotel->timezone ?? '+06:00' }}</td>
                                            </tr>
                                            <tr>
                                                <td>@lang('admin.city')</td>
                                                <td>{{ $hotel->city }}</td>
                                            </tr>
                                            <tr>
                                                <td>@lang('admin.address')</td>
                                                <td>{{ $hotel->__('address') }}</td>
                                            </tr>
                                            <tr>
                                                <td>ID</td>
                                                <td>{{ $hotel->id }}</td>
                                            </tr>
                                            @if($hotel->currency)
                                                <tr>
                                                    <td>@lang('main.currency')</td>
                                                    <td>{{ $hotel->currency }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td>@lang('admin.checkin')</td>
                                                <td>{{ $hotel->checkin }}</td>
                                            </tr>
                                            <tr>
                                                <td>@lang('admin.checkout')</td>
                                                <td>{{ $hotel->checkout }}</td>
                                            </tr>
                                            <tr>
                                                <td>@lang('admin.rating')</td>
                                                <td>
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
                                                </td>
                                            </tr>
                                            @isset($hotel->amenity->services)
                                                <tr>
                                                    <td>@lang('admin.amenities')</td>
                                                    <td>{{ $hotel->amenity->services ?? '' }}</td>
                                                </tr>
                                            @endisset
                                            <tr>
                                                <td>@lang('admin.status')</td>
                                                <td>
                                                    @if($hotel->status == 1)
                                                        <span style="color: var(--green)">@lang('admin.active')</span>
                                                    @else
                                                        <span style="color: #f35252">@lang('admin.disable')</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="add-wrap">
                                    <button type="button" class="btn more add pull-right" data-bs-toggle="modal"
                                            data-bs-target="#createUserModal">@lang('admin.add')
                                    </button>
                                    <div class="profile-wrap">
                                        <h6>{{$users->name}} {{$users->lastname}}</h6>
                                        <p><a href="{{ route('users.listHotel')}}">@lang('admin.all_users')</a></p>
                                        <div class="email">{{ $users->email }}</div>
                                        <div class="phone">{{ $users->phone }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9">
                                @if($hotel->description)
                                    <div class="dashboard-item" style="margin-top: 40px">
                                        <h4>@lang('admin.description')</h4>
                                        <div class="descr">{!! $hotel->__('description') !!}</div>
                                    </div>
                                @endif
                                <div class="dashboard-item">
                                    <div class="images">
                                        <div class="row">
                                            <div class="col-md-6">
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
                                                            <div class="col-md-6">
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
                                                                            <img src="{{ route('index') }}/img/icons/trash.svg" style="height: auto; margin: 0;">
                                                                        </button>
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
                                    <div class="table-wrap">
                                        <table>
                                            <tr>
                                                <td style="border-top: none">@lang('admin.phone_number')</td>
                                                <td style="border-top: none">{{ $hotel->phone }}</td>
                                            </tr>
                                            <tr>
                                                <td>Email</td>
                                                <td>{{ $hotel->email }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно -->
        <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <form id="createUserForm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>@lang('admin.add_user')</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"><i
                                        class="fa-solid fa-close"></i></button>
                        </div>
                        <div class="modal-body">
                            @csrf
                            <div class="row">
                                <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
                                <div class="mb-3 col-md-6">
                                    <label for="name" class="form-label">@lang('main.name')</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="email" class="form-label">@lang('main.phone')</label>
                                    <input type="tel" name="phone" class="phone" id="phone">
                                </div>
                                <div class="mb-3 col-md-6">
                                    @php
                                        $allowed = ["Hotel Accountent", "Hotel Manager", "Super Admin"];
                                        $filteredRoles = array_filter($roles, function ($role) use ($allowed) {
                                            return in_array($role, $allowed);
                                        });
                                    @endphp
                                    <label for="email" class="form-label">@lang('admin.role')</label>
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
                                {{--                                <div class="mb-3 mt-3 col-md-12">--}}
                                {{--                                    <small class="form-text text-pass">--}}
                                {{--                                        @lang('main.password_help')--}}
                                {{--                                    </small>--}}
                                {{--                                </div>--}}
                                <div class="mb-3 col-md-6">
                                    <div class="input-group">
                                        <label for="password" class="form-label">@lang('main.password')</label>
                                        <input type="password" class="form-control password-field" id="password"
                                               name="password" required>
                                    </div>

                                    {{--                                    <div id="passwordHelp"--}}
                                    {{--                                         class="form-text text-danger d-none">@lang('main.password_not_regex')</div>--}}
                                </div>
                                <div class="mb-3 col-md-6
                                ">

                                    <div class="input-group">
                                        <label for="password"
                                               class="form-label">@lang('admin.password_confirmation')</label>
                                        <input type="password" id="password_confirmation" name="password_confirmation"
                                               class="password-field" required>
                                    </div>

                                    <div id="confirmHelp"
                                         class="form-text text-danger d-none">@lang('main.password_confirm')</div>
                                </div>
                                <div class="mb3 col-md-12 d-flex justify-content-end" style="gap: 20px">
                                    <button type="button" class="btn"
                                            onclick="generatePassword()">@lang("admin.generate_password")</button>
                                    <button type="button" class="btn"
                                            id="togglePasswordVisibility">@lang('main.show_password')
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn more">@lang('admin.create')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script>
            document.getElementById('togglePasswordVisibility').addEventListener('click', function () {
                const fields = document.querySelectorAll('.password-field');
                let showing = false;
                let showingText = @json(__('admin.hide_password'));
                let hiddenText = @json(__('main.show_password'));

                fields.forEach(field => {
                    if (field.type === 'password') {
                        field.type = 'text';
                        showing = true;
                    } else {
                        field.type = 'password';
                    }
                });

                this.textContent = showing ? showingText : hiddenText;
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('createUserForm');

                form.addEventListener('submit', function (e) {
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

                            if (data.status === 200) {
                                alert('Пользователь успешно создан!');
                                document.getElementById('createUserModal').classList.remove('show');
                                // form.reset();
                                location.reload();
                            }

                            if (data.status === 500) {
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

            document.getElementById('createUserForm').addEventListener('submit', function (e) {
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
            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const target = btn.getAttribute('data-bs-target');
                    document.querySelector(target).classList.add('show');
                });
            });

            // Закрыть модалку
            document.querySelectorAll('.btn-close, [data-bs-dismiss="modal"]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    btn.closest('.modal').classList.remove('show');
                });
            });

            // Закрыть модалку по клику на фон
            document.querySelectorAll('.modal').forEach(function (modal) {
                modal.addEventListener('click', function (e) {
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
