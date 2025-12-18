@extends('layouts.master')

@php
    use App\Models\Hotel;
    use App\Models\Room;
    use App\Models\Rate;
    use App\Models\CancellationRule;
    use Carbon\Carbon;
@endphp

@section('title', 'Оформление заказа')

@section('content')
    @auth
        @php
            $hotel = Hotel::where('exely_id', $request->propertyId)
                          ->orWhere('id', $request->propertyId)
                          ->firstOrFail();

            $room  = Room::findOrFail($request->room_id);
            $rate  = Rate::findOrFail($request->rate_id);

            $cancel   = CancellationRule::where('rate_id', $rate->id)->first();
            $cancelId = $cancel?->id ?? 0; // ✅ fix

            $hotelTz   = $hotel->timezone ?: 'UTC';
            $hotel_utc = Carbon::now($hotelTz)->format('P');

            // Child ages guard
            $childAges = is_array($request->childAges)
                ? $request->childAges
                : (empty($request->childAges) ? [] : explode(',', (string)$request->childAges));
        @endphp

        <style>
            .check input { width: auto; }
            body {
                font-family: Unbounded, sans-serif !important;
                background-color: rgba(246, 246, 246, 1) !important;
            }
            .page { padding-bottom: 60px; }
        </style>

        <div class="page order">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>
                            <img src="{{ route('index') }}/img/arrow-left.svg" alt="">
                            @lang('main.booking')
                        </h1>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-12">
                        <div class="sidebar">
                            @if($hotel->image)
                                <img src="{{ Storage::url($hotel->image) }}" alt="">
                            @else
                                <img src="{{ route('index')}}/img/noimage.png" alt="">
                            @endif

                            <div class="text-wrap">
                                <div class="descr">@lang('main.hotel'): {{ $hotel->__('title') }}</div>
                                <div class="descr">@lang('main.room'): {{ $room->__('title') }}</div>
                                <div class="descr">@lang('main.rate'): {{ $rate->__('title') }}</div>

                                <div class="date">
                                    @lang('main.check-in/check-out'):
                                    {{ $arrival }} {{ $hotel->checkin }} - {{ $departure }} {{ $hotel->checkout }}
                                    (UTC {{ $hotel_utc }})
                                </div>

                                <div class="cancel">
                                    {{ $request->cancelText ?? '' }} {{ $request->cancelPrice ?? 0 }} {{ $request->currency ?? '' }}
                                </div>

                                <div class="row mt">
                                    <div class="col-md-8 col-8">
                                        <div class="total">@lang('main.total')</div>
                                    </div>
                                    <div class="col-md-4 col-4">
                                        <div class="price">{{ round($request->sum) }} {{ $request->currency }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 col-md-12">
                        <h5>@lang('main.trip')</h5>

                        <form action="{{ route('book_verify') }}">
                            @csrf
                            <input type="hidden" name="propertyId" value="{{ $request->propertyId }}">
                            <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                            <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                            <input type="hidden" name="room_id" value="{{ $request->room_id }}">
                            <input type="hidden" name="rate_id" value="{{ $request->rate_id }}">
                            <input type="hidden" name="meal_id" value="{{ $request->meal_id }}">
                            <input type="hidden" name="roomCount" value="{{ $request->roomCount ?? 1 }}">

                            @foreach ($childAges as $age)
                                <input type="hidden" name="childAges[]" value="{{ $age }}">
                            @endforeach

                            {{-- ✅ fix --}}
                            <input type="hidden" name="cancellation_id" value="{{ $cancelId }}">

                            <input type="hidden" name="cancelText" value="{{ $request->cancelText }}">
                            <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                            <input type="hidden" name="sum" value="{{ round($request->sum) }}">
                            <input type="hidden" name="currency" value="{{ $request->currency }}">
                            <input type="hidden" name="source_sym" value="{{ $request->source_sym }}">

                            @for ($i = 1; $i <= (int)$request->adult; $i++)
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>
                                            @if($i === 1)
                                                @lang('main.full_name')
                                            @else
                                                #{{ $i }} @lang('main.full_name')
                                            @endif
                                        </label>
                                        <input type="text"
                                               name="title{{ $i }}"
                                               placeholder="@lang('main.full_name')"
                                               value="{{ $i === 1 && Auth::check() ? Auth::user()->name : '' }}"
                                               required>
                                    </div>
                                </div>
                            @endfor

                            @for ($i = 1; $i <= (int)$request->child; $i++)
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="label">
                                            #{{ $i }} @lang('main.full_name') @lang('main.child')
                                        </div>
                                        <input type="text" name="child_name{{ $i }}" placeholder="Усенов У.У." required>
                                    </div>
                                </div>
                            @endfor

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="label">@lang('main.count_adult')</div>
                                        <input type="text" name="adult" value="{{ (int)$request->adult }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="label">@lang('main.count_child')</div>
                                        <input type="text" name="child" value="{{ (int)$request->child }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>@lang('main.phone')</label>
                                        <input type="text" name="phone" id="phone"
                                               style="padding-left: 50px"
                                               value="{{ Auth::user()->phone ?? '' }}"
                                               required>
                                        <div id="output"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" value="{{ Auth::user()->email ?? '' }}" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        @include('auth.layouts.error', ['fieldname' => 'comment'])
                                        <label>@lang('main.message')</label>
                                        <input type="text" name="comment">
                                    </div>
                                </div>
                            </div>

                            <div class="line"></div>

                            <div class="descr">
                                @if(app()->getLocale() == 'ru')
                                    Нажимая кнопку ниже, я принимаю условия...
                                @else
                                    By clicking the button below, I accept the terms...
                                @endif
                            </div>

                            <div class="btn-wrap">
                                @hasrole('Demo')
                                <div class="alert alert-danger">Доступ ограничен</div>
                                @else
                                    <button class="more" id="saveBtn">@lang('main.confirm')</button>
                                    @endhasrole
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeout Modal --}}
        <div class="modal fade" id="timeoutModal" tabindex="-1" aria-labelledby="timeoutLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center">
                    @if(app()->getLocale() == 'ru')
                        <div class="modal-header">
                            <h5 class="modal-title" id="timeoutLabel">Время истекло</h5>
                        </div>
                        <div class="modal-body">
                            Время бронирования истекло. Вы будете перенаправлены на поиск.
                        </div>
                    @else
                        <div class="modal-header">
                            <h5 class="modal-title" id="timeoutLabel">Time has expired</h5>
                        </div>
                        <div class="modal-body">
                            The booking time has expired. You will be redirected to the search page.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            setTimeout(function () {
                const modal = new bootstrap.Modal(document.getElementById('timeoutModal'));
                modal.show();
                setTimeout(function () {
                    window.location.href = "{{ route('index') }}";
                }, 4000);
            }, 600000);
        </script>

    @else
        @include('layouts.auth')
    @endauth
@endsection