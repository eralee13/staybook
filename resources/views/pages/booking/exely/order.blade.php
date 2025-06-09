@php use App\Models\Hotel; @endphp
@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3><a href="search.html"><img src="{{ route('index') }}/img/icons/arrow-left.svg" alt=""></a>
                        Подтвердите и оплатите
                    </h3>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-md-12 order-xl-1 order-lg-1 order-2">
                    <h5>Ваша поездка</h5>
                    <form action="{{ route('book_verify_exely') }}">
                        <input type="hidden" name="propertyId" value="{{ $request->propertyId }}">
                        <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                        <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                        <input type="hidden" name="ratePlanId" value="{{ $request->ratePlanId }}">
                        <input type="hidden" name="roomTypeId" value="{{ $request->roomTypeId }}">
                        <input type="hidden" name="adultCount" value="{{ $request->adultCount }}">
                        <input type="hidden" name="placements" value="{{ $request->placements }}">
                        @if (request()->filled('childAges'))
                            <input type="hidden" name="childAges[]" value="{{ implode(',', $childs) }}">
                        @endif
                        <input type="hidden" name="checkSum" value="{{ $request->checkSum }}">
                        <input type="hidden" name="servicesId" value="{{ $request->servicesId }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="label">ФИО</div>
                                    <input type="text" name="name" placeholder="Асанов А.А."
                                           value="{{ Auth::user()->name }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="label">Гости</div>
                                    <input type="text" value="{{ $request->adultCount }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Кол-во детей</label>
                                    @if (request()->filled('childAges'))
                                        <input type="text" value="{{ count($childs) }}" readonly>
                                    @else
                                        <input type="text" value="0" readonly>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Номер телефона</label>
                                    <input type="text" name="phone" id="phone" value="{{ Auth::user()->phone }}"
                                           required>
                                    <div id="output"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input type="email" name="email" value="{{ Auth::user()->email }}" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'comment'])
                                    <label for="">Комментарий</label>
                                    <textarea name="comment" rows="3">Ваш комментарий</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="line"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Варианты оплаты</h5>
                                <div class="method-item current">
                                    <div class="name">Оплатить
                                        сейчас {{ $request->price }} {{ $request->currency ?? '$' }}</div>
                                </div>
                                {{--                                <div class="method-item">--}}
                                {{--                                    <div class="name">Оплатите часть сейчас, а остаток внесите позже--}}
                                {{--                                        36,000 сом к оплате сегодня, 36,000 сом — 01 мар. 2025 г.</div>--}}
                                {{--                                </div>--}}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row payment-wrap">
                                    <div class="col-md-6">
                                        <h5>Оплата</h5>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="payment">
                                            <div class="payment-item">
                                                <img src="{{ route('index') }}/img/balance.svg" alt="">
                                            </div>
                                            <div class="payment-item">
                                                <img src="{{ route('index') }}/img/mega.svg" alt="">
                                            </div>
                                            <div class="payment-item">
                                                <img src="{{ route('index') }}/img/optima.svg" alt="">
                                            </div>
                                            <div class="payment-item">
                                                <img src="{{ route('index') }}/img/mbank.svg" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="payment-type">
                                    <select name="" class="payment_type" id="">
                                        <option value="">Выбрать способ оплаты</option>
                                        <option value="">Balance</option>
                                        <option value="">Mega</option>
                                        <option value="">Optima</option>
                                        <option value="">Mbank</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="line"></div>
                        <div class="descr">Нажимая кнопку ниже, я принимаю условия (Правила дома, установленные
                            хозяином, Основные правила для гостей, Правила StayBook в отношении повторного бронирования
                            и возврата средств, Условия частичной предоплаты) и соглашаюсь, что StayBook может списать
                            средства с моего способа оплаты, если ответственность за ущерб лежит на мне.
                        </div>
                        <div class="btn-wrap">
                            <button class="more" id="saveBtn">Подтвердить и оплатить</button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-md-12 order-xl-2 order-lg-2 order-1">
                    <div class="sidebar">
                        <div class="row">
                            <div class="col-md-4">
                                <img src="{{ Storage::url($hotel->image) }}" alt="">
                            </div>
                            <div class="col-md-8">
                                <div class="descr">Отель: {{ $hotel->title }}</div>
                                <div class="descr">{{ $request->categoryName }}</div>
                                <div class="date">Время заезда/выезда: {{ $arrival }} {{ $hotel->checkin }}
                                    - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $hotel_utc }})
                                </div>
                                <div class="cancel">Правила отмены:
                                    @if($request->cancelPossible == true)
                                        Бесплатная отмена действует до {{ $request->cancelDate }} ({{ $offset }}). Размер штрафа: {{ $request->cancelPrice }} {{ $request->currency }}
                                    @else
                                        Возможность бесплатной отмены отсутствует. Размер
                                        штрафа: {{ $request->cancelPrice }} {{ $request->currency }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="line"></div>
                        <div class="row mt">
                            <div class="col-md-8">
                                <div class="total">Итого</div>
                            </div>
                            <div class="col-md-4">
                                <div class="price">{{ $request->price }} {{ $request->currency }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #phone {
            padding-left: 50px;
        }
    </style>

@endsection
