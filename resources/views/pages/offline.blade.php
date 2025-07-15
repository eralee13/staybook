@extends('layouts.filter_mini')

@section('title', 'Offline')

@section('content')

    @auth()
    @vite(['resources/js/app.js', 'resources/css/app.css'])

    <style>
        form {
            background-color: #fff;
            border-radius: 20px;
            padding: 20px;
        }

        .main-filter form input, .main-filter form select {
            padding-left: 10px;
        }

        .noUi-connect {
            background-color: #0161ae;
        }
        .btn-check:active+.btn-outline-primary, .btn-check:checked+.btn-outline-primary, .btn-outline-primary.active, .btn-outline-primary.dropdown-toggle.show, .btn-outline-primary:active{
            background-color: #0161ae !important;
            border-color: #0161ae !important;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.1/css/bootstrap.min.css"
          integrity="sha512-Ez0cGzNzHR1tYAv56860NLspgUGuQw16GiOOp/I2LuTmpSK9xDXlgJz3XN4cnpXWDmkNBKXR/VDMTCnAaEooxA=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <div class="page main-filter offline">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="type">
                        <div class="type-item">
                            <a href="{{route('index')}}">@lang('main.hotels_and_rooms')</a>
                        </div>
                        <div class="type-item current">
                            <a href="{{ route('offline') }}">@lang('main.offline')</a>
                        </div>
                    </div>
                    <form action="{{ route('offline_send') }}" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="">@lang('main.checkin')</label>
                                    <input type="text" id="date" class="date">
                                    <input type="hidden" id="arrivalDate" name="arrivalDate"
                                           value="{{ now()->format('Y-m-d') }}">
                                    <input type="hidden" id="departureDate" name="departureDate"
                                           value="{{ $tomorrow }}">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="form-group">
                                    <label for="">@lang('main.city')</label>
                                    <select name="city" id="city">
                                        <option value="Bishkek">Bishkek</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->title }}">{{ $city->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('city')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h4>@lang('main.hotel_name')</h4>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">@lang('main.type')</label>
                                    <select name="type" id="">
                                        <option value="">@lang('admin.choose')</option>
                                        <option value="Отель">Отель</option>
                                        <option value="Апарт отель">Апарт отель</option>
                                        <option value="Гостевой дом">Гостевой дом</option>
                                    </select>
                                    @error('type')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rating">@lang('admin.rating')</label>
                                    <select name="rating" id="rating">
                                        <option value="">@lang('admin.choose')</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                    @error('rating')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h4>@lang('main.rooms')</h4>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">@lang('main.count_room')</label>
                                    <select name="room_count" id="room_count" class="form-select">
                                        <option value="">@lang('admin.choose')</option>
                                        @for($i = 1; $i <= 8; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('room_count')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">@lang('main.price_night') <span class="text-muted">{{ $fxBase }}</span></label>
                                    <input type="hidden" name="currency" value="{{ $fxBase }}">
                                    <div id="price-slider" class="mb-2"></div>
                                    <div class="d-flex justify-content-between">
                                        <input type="number" id="min-price" name="min_price" class="form-control"
                                               style="max-width: 100px" value="14500">
                                        <input type="number" id="max-price" name="max_price" class="form-control"
                                               style="max-width: 100px" value="71100">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">@lang('admin.meals')</label>
                                    <divad class="btn-group" role="group" aria-label="Meal options">
                                        @php
                                            $meals = ['ALL' => 'ALL', 'BB' => 'BB', 'FB' => 'FB', 'HB' => 'HB', 'RO' => 'RO'];
                                        @endphp

                                        @foreach ($meals as $key => $label)
                                            <input type="checkbox" class="btn-check" id="meal-{{ $key }}" name="meal[]"
                                                   value="{{ $key }}" autocomplete="off">
                                            <label class="btn btn-outline-primary"
                                                   for="meal-{{ $key }}">{{ $label }}</label>
                                        @endforeach
                                    </divad>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h4>@lang('main.accommodation_type')</h4>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">@lang('main.accommodation_type')</label>
                                    <select name="accommodation" id="">
                                        <option>@lang('admin.choose')</option>
                                        <option value="Single">Single</option>
                                        <option value="Twin">Twin</option>
                                        <option value="Double">Double</option>
                                    </select>
                                    @error('accommodation')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">@lang('main.room_type')</label>
                                    <select name="type_room" id="">
                                        <option>@lang('admin.choose')</option>
                                        <option value="Suite">Suite</option>
                                        <option value="Бизнес-Студия">Бизнес-Студия</option>
                                        <option value="1-категория">1-категория</option>
                                        <option value="2-категория">2-категория</option>
                                        <option value="3-категория">3-категория</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h4>@lang('main.guests')</h4>
                            </div>
                            <div class="col-md-2 col-6 mb-3">
                                <label class="form-label">@lang('main.count_adult')</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateGuests('adult', -1)">−</button>
                                    <input type="text" id="adult-count" name="adult" class="form-control text-center" value="1" readonly>
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateGuests('adult', 1)">+</button>
                                </div>
                            </div>

                            <!-- Дети -->
                            <div class="col-md-2 col-6 mb-3">
                                <label class="form-label">@lang('main.count_child')</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateGuests('child', -1)">−</button>
                                    <input type="text" id="child-count" name="child" class="form-control text-center" value="0" readonly>
                                    <button type="button" class="btn btn-outline-secondary" onclick="updateGuests('child', 1)">+</button>
                                </div>
                            </div>

                            <!-- Возраст детей -->
                            <div class="col-md-8 mb-3">
                                <label class="form-label">@lang('main.children_age')</label>
                                <div id="child-ages" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h4>@lang('main.contacts')</h4>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">@lang('main.name')</label>
                                    <input type="text" name="name">
                                    @error('name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">@lang('main.phone')</label>
                                    <input type="text" id="phone" name="phone">
                                    @error('phone')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input type="email" name="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('main.message')</label>
                                    <textarea name="message" cols="30" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('main.upload_file')</label>
                                    <input type="file" name="file">
                                </div>
                            </div>
                        </div>
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <button class="more" id="send">@lang('main.create_book')</button>
                            </div>
                            <div class="col-md-8">
                                <div class="btn-wrap">
                                    <a href="{{ route('offline') }}">@lang('main.clear')</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateGuests(type, delta) {
            const input = document.getElementById(`${type}-count`);
            let value = parseInt(input.value, 10);

            if (isNaN(value)) value = 0;
            value += delta;

            // Минимум 1 взрослый, минимум 0 детей
            if (type === 'adult' && value < 1) value = 1;
            if (type === 'child' && value < 0) value = 0;

            input.value = value;

            if (type === 'child') renderChildAgeInputs(value);
        }

        function renderChildAgeInputs(count) {
            const container = document.getElementById('child-ages');

            // Сохраняем текущие значения
            const previousValues = Array.from(container.querySelectorAll('input'))
                .map(input => input.value);

            container.innerHTML = '';

            for (let i = 0; i < count; i++) {
                const input = document.createElement('input');
                input.type = 'number';
                input.name = `childAges[]`;
                input.classList.add('form-control');
                input.placeholder = 'Возраст';
                input.min = 0;
                input.max = 17;
                input.required = true;
                input.style.width = '80px';

                // Устанавливаем сохранённое значение, если есть
                if (previousValues[i] !== undefined) {
                    input.value = previousValues[i];
                }

                container.appendChild(input);
            }
        }
    </script>
    @else
        @include('layouts.auth')
    @endauth

@endsection
