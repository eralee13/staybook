<div class="row">
                <div class="col-lg-8 col-md-12 order-xl-1 order-lg-1 order-2">
                    <h5>Ваша поездка</h5>
                    <form action="{{ route('book_verify') }}">
                        <input type="hidden" name="propertyId" value="{{ $request->propertyId }}">
                        <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                        <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                        <input type="hidden" name="room_id" value="{{ $request->room_id }}">
                        <input type="hidden" name="rate_id" value="{{ $request->rate_id }}">
                        <input type="hidden" name="meal_id" value="{{ $request->meal_id }}">
                        {{-- <input type="hidden" name="roomType" value="{{ $request->roomType }}">--}}
                        {{-- <input type="hidden" name="roomCount" value="{{ $request->roomCount }}">--}}
                        {{-- <input type="hidden" name="roomCode" value="{{ $request->roomCode }}">--}}
                        {{-- <input type="hidden" name="placementCode" value="{{ $request->placementCode }}">--}}
                        @if(!empty($request->rooms) && is_array($request->rooms))
                            @foreach($request->rooms as $i => $room)
                                <input
                                        type="hidden"
                                        name="rooms[{{ $i }}][adults]"
                                        value="{{ $room['adults'] }}"
                                >
                                @if(!empty($room['childAges']) && is_array($room['childAges']))
                                    @foreach($room['childAges'] as $j => $age)
                                        <input
                                                type="hidden"
                                                name="rooms[{{ $i }}][childAges][{{ $j }}]"
                                                value="{{ $age }}"
                                        >
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                        <input type="hidden" name="cancellation_id" value="{{ $request->cancellation_id }}">
                        <input type="hidden" name="cancelDate" value="{{ $request->cancelDate }}">
                        <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                        <input type="hidden" name="price" value="{{ $request->price }}">

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
                                    <input type="text" value="{{ $request->adult }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Кол-во детей</label>
                                    <input type="text" value="{{ $request->child }}" readonly>
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
                    @php
                        
                        $hotel = Hotel::where('exely_id', $request->propertyId)->orWhere('id', $request->propertyId)->first();
                        $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                        $cancel_utc = \Carbon\Carbon::createFromDate($request->cancelDate)->format('P');
                        $cancel = \App\Models\CancellationRule::where('id', $request->cancellation_id)->firstOrFail();
                        $room = \App\Models\Room::where('id', $request->room_id)->firstOrFail();
                        $rate = \App\Models\Rate::where('id', $request->rate_id)->firstOrFail();
                        
                    @endphp
                    <div class="sidebar">
                        <div class="row">
                            <div class="col-md-4">
                                <img src="{{ Storage::url($hotel->image) }}" alt="">
                            </div>
                            <div class="col-md-8">
                                <div class="descr">Отель: {{ $hotel->title }}</div>
                                <div class="descr">Номер: {{ $room->title }}</div>
                                <div class="descr">Тариф: {{ $rate->title }}</div>
                                <div class="date">Время заезда/выезда: {{ $arrival }} {{ $hotel->checkin }}
                                    - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $hotel_utc }})
                                </div>
                                <div class="cancel">Правила отмены:
                                    @if($cancel->is_refundable == 1)
                                        @if(now() <= $request->cancelDate)
                                            Бесплатная отмена действует до {{ $request->cancelDate }}
                                            (UTC {{ $cancel_utc }}).
                                        @endif
                                        Размер штрафа: {{ $request->cancelPrice }} {{ $request->currency ?? '$' }}
                                    @else
                                        Возможность бесплатной отмены отсутствует. Размер
                                        штрафа: {{ $request->cancelPrice }} {{ $request->currency ?? '$' }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        {{--                        <div class="line"></div>--}}
                        {{--                        <h5>Детализация цены</h5>--}}
                        {{--                        <div class="row">--}}
                        {{--                            <div class="col-md-8">--}}
                        {{--                                <div class="price-item">--}}
                        {{--                                    <div class="name">36,000 {{ $request->currency }} * 2 ночи</div>--}}
                        {{--                                </div>--}}
                        {{--                            </div>--}}
                        {{--                            <div class="col-md-4">--}}
                        {{--                                <div class="price">{{ $request->price }} {{ $request->currency }}</div>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                        <div class="line"></div>
                        <div class="row mt">
                            <div class="col-md-8">
                                <div class="total">Итого</div>
                            </div>
                            <div class="col-md-4">
                                <div class="price">{{ $request->price }} {{ $request->currency ?? '$'}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>