@extends('auth.layouts.master')

@section('title', 'Квоты')

<meta name="csrf-token" content="{{ csrf_token() }}">
@vite(['resources/css/app.css', 'resources/js/bookcalendar.js'])
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
                    <a class="current" href="{{ route('bookcalendar.index', $hotel) }}">Квоты</a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('bookcalendarprice.index', $hotel) }}">Цены</a>
                </div>
            </div>
            <div class="row select-hotel">
                <div class="col-md-8" style="margin-top: 20px">
                    <div class="form-group">
                        <label for="">Выберите отель</label>
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

    @if($hotel && $hotel->exely_id != null)

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
                        <ul>
                            <li><strong id="modalHotelName">—</strong></li>
                            <li><strong id="modalRoomName">—</strong></li>
                            <li><strong id="modalRateName">—</strong></li>
                        </ul>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createBookingForm" action="{{ route('bookcalendar.create') }}" method="post">
                            <div id="bookingError" class="alert alert-danger d-none" role="alert"></div>

                            <input type="hidden" name="hotel_id" id="modalHotelId">
                            <input type="hidden" name="rate_id" id="modalRateId">
                            <input type="hidden" name="room_id" id="modalRoomId">
                            @csrf
                            <div class="form-group">
                                <label for="modalDateRange" class="form-label">Диапазон дат</label>
                                <input type="text" id="modalDateRange" class="date" required="">
                                <input type="hidden" id="arrivalDate" name="arrivalDate"
                                       value="{{ now() }}">
                                <input type="hidden" id="departureDate" name="departureDate"
                                       value="{{ now()->addDay() }}">
                            </div>

                            <div class="form-group">
                                <label for="modalAllotment" class="form-label">Квота</label>
                                <input type="number" class="form-control" id="modalAllotment" name="allotment" value="1"
                                       required>
                            </div>

                            <button type="submit" class="more">Создать</button>
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