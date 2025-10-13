@extends('auth.layouts.master')

@section('title', __('admin.rates_and_availability'))

@vite(['resources/css/app.css', 'resources/js/bookcalendarprice.js'])
{{-- @livewireStyles --}}

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JS + Popper -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

@section('content')
    <div class="page">
        <div class="container-fluid">
            <div class="row list-btn">
                <div class="col-md-4">
                    <a href="{{ route('bookcalendar.index', $hotel) }}">Квоты</a>
                </div>
                <div class="col-md-4">
                    <a class="current" href="{{ route('bookcalendarprice.index', $hotel) }}">Цены</a>
                </div>
            </div>
            <div class="row select-hotel">
                <div class="col-md-8" style="margin-top: 20px">
                    <div class="form-group">
                        <label for="">Выберите отель</label>
                        <div class="custom-select">
                            <select name="hotel_id" id="hotel_id"
                                    onchange="window.location.href = '{{ route(Route::currentRouteName(), ['hotel' => '__HOTEL__']) }}'.replace('__HOTEL__', this.value)">
                                @foreach ($hotelslist as $hotel)
                                    <option value="{{ $hotel->id }}" {{ request()->route('hotel') == $hotel->id ? 'selected' : '' }}>
                                        {{ $hotel->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="status-wrap">
                        <div class="status">
                            <span class="status-label" style="display: flex; align-items: center;">
                                <div class="status-color"></div>
                                Нет квот
                            </span>
                        </div>
                        <div class="status">
                            <span class="status-label" style="display: flex; align-items: center;">
                                <div class="status-color" style="background-color: #7EB554;"></div>
                                Есть квоты
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @php
                use Carbon\Carbon;
                // Добавляем 1 месяц вперёд и устанавливаем на 1 и 2 число
                $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

                // Получаем текущую локаль Laravel
                $locale = app()->getLocale();
                $id = $request->route('hotel');
                $hotel = \App\Models\Hotel::where('id', $id)->first();
            @endphp

            <script src="https://code.jquery.com/jquery-3.7.1.min.js"
                    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
                    crossorigin="anonymous"></script>

            <script>
                $(function () {
                    const locale = "{{ $locale }}";

                    // локализация для разных языков
                    const localeSettings = {

                        ru: {
                            format: 'YYYY-MM-DD',
                            separator: ' - ',
                            applyLabel: 'Применить',
                            cancelLabel: 'Отмена',
                            fromLabel: 'С',
                            toLabel: 'По',
                            weekLabel: 'Н',
                            customRangeLabel: 'Выбрать вручную',
                            daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                            monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                                'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                            firstDay: 1,

                        }
                    };

                });
            </script>

            <div id="calendar"></div>
        </div>
    </div>

    @if($hotel->exely_id != null)

    @else
        <!-- Modal -->
        <div class="modal fade" id="createBookingModal" tabindex="-1" aria-labelledby="createBookingLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Создание брони для:
                        </h5>
                        <ul class="list-unstyled small text-muted mb-2">
                            <li><strong id="modalHotelName">—</strong></li>
                            <li><strong id="modalRoomName">—</strong></li>
                            <li><strong id="modalRateName">—</strong></li>
                        </ul>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <meta name="csrf-token" content="{{ csrf_token() }}">

                        <form id="createBookingForm" action="{{ route('bookcalendarprice.create') }}" method="post">

                            @csrf

                            <input type="hidden" name="hotel_id" id="modalHotelId">
                            <input type="hidden" name="rate_id"  id="modalRateId">   {{-- пример: 5_p4 или просто 5 --}}
                            <input type="hidden" name="room_id"  id="modalRoomId">

                            {{-- даты --}}
                            <div class="form-group">
                                <label for="modalDateRange" class="form-label">Диапазон дат</label>
                                <input type="text" id="modalDateRange" class="form-control" placeholder="YYYY-MM-DD — YYYY-MM-DD" required>
                                <input type="hidden" id="start" name="start" value="{{ now()->toDateString() }}">
                                <input type="hidden" id="end"   name="end"   value="{{ now()->addDay()->toDateString() }}">
                            </div>

                            {{-- ЧЕТЫРЕ ПОЛЯ ЦЕНЫ — КЛЮЧЕВОЕ! --}}
                            <div class="form-group">
                                <label class="form-label d-block">Стоимость за ночь</label>
                                <div class="row g-2">
                                    <div class="col-6 col-md-3">
                                        <label class="form-text">1 гость</label>
                                        <input type="number" class="form-control" id="price1" name="price"  min="0" step="1" placeholder="например 60">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-text">2 гостя</label>
                                        <input type="number" class="form-control" id="price2" name="price2" min="0" step="1" placeholder="например 90">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-text">3 гостя</label>
                                        <input type="number" class="form-control" id="price3" name="price3" min="0" step="1" placeholder="например 120">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-text">4 гостя</label>
                                        <input type="number" class="form-control" id="price4" name="price4" min="0" step="1" placeholder="например 150">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="more mt-3">Сохранить</button>
                        </form>


                    </div>

                </div>
            </div>
        </div>
    @endif

@endsection

<style>
    .fc-datagrid-cell-main {
        white-space: pre-line;
    }

    .fc-datagrid-cell-main b {
        margin-bottom: 5px;
        display: inline-block;
    }

    .fc-h-event .fc-event-main-frame {
        flex-direction: column
    }

    .fc-event-main {
        padding: 5px;
    }

    .fc-datagrid-cell-main {
        display: inline-block;
    }

    .fc-event-title,
    .fc-event-main {
        display: block !important;
        text-align: center;
        font-size: 14px;
        font-weight: bold;
    }
</style>

<script>
    window.resourcesData = @json($resources);
    window.eventsData = @json($events);
</script>

{{-- @livewireScripts --}}