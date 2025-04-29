@extends('layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@vite(['resources/css/app.css', 'resources/js/bookcalendar.js'])
{{-- @livewireStyles --}}

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JS + Popper -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

@section('content')
    <div class="container-fluid mt-5  mb-5">
        {{-- {{ print_r($resources) }} --}}
        {{-- {{ print_r($events) }} --}}
        <div class="status-container" style="display: flex; justify-content: space-around; align-items: center;">
            <div class="e-search">
                <form action="">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="">@lang('main.search-hotel')</label><br>
                                <select name="hotel_id" id="hotel_id" class="form-control" style="width: 200px">
                                    <option value="908">Все @lang('main.hotels')</option>
                                    @foreach ($hotelslist as $hotel)
                                        <option value="{{ $hotel->id }}">{{ $hotel->title_en }}</option>
                                    @endforeach
                                    
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="">@lang('main.search-date')</label><br>
                                <input type="text" id="daterange" class="da" autocomplete="off" placeholder="Выберите дату" style="width: auto">
                                {{-- <button type="submit" class="btn btn-primary more" style="margin-left: 10px;">@lang('main.search')</button> --}}
                            </div>  
                        </div>
                    </div>
                    @php
                        use Carbon\Carbon;

                        // Добавляем 1 месяц вперёд и устанавливаем на 1 и 2 число
                        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                        $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');


                        // Получаем текущую локаль Laravel
                        $locale = app()->getLocale(); // 'ru', 'en', и т.д.
                    @endphp

                    <script>
                        $(function() {
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
                                    customRangeLabel: 'Свой',
                                    weekLabel: 'Н',
                                    customRangeLabel: 'Выбрать вручную',
                                    daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                                    monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 
                                                 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                                    firstDay: 1,
                                    
                                }
                            };
                    
                            $('#daterange').daterangepicker({
                                autoUpdateInput: true,
                                autoApply: true,
                                startDate: "{{ $startDate }}",
                                endDate: "{{ $endDate }}",
                                locale: localeSettings[locale] || localeSettings['en'],
                            });
                        });

                        
                        
                    </script>
                </form>
            </div>
            <div style="width: 30%"></div>
            <div class="status" style="display: flex; align-items: center; margin-right: 20px;">
                <span class="status-label" style="display: flex; align-items: center;">
                    <div class="status-color" style="background-color: #d95d5d; width: 15px; height: 15px; margin-right: 5px;"></div>
                    Забронирован
                </span>
            </div>
            <div class="status" style="display: flex; align-items: center; margin-right: 20px;">
                <span class="status-label" style="display: flex; align-items: center;">
                    <div class="status-color" style="background-color: #39bb43; width: 15px; height: 15px; margin-right: 5px;"></div>
                    Свободен
                </span>
            </div>
            <div class="status" style="display: flex; align-items: center;">
                <span class="status-label" style="display: flex; align-items: center;">
                    <div class="status-color" style="background-color: #e19d22; width: 15px; height: 15px; margin-right: 5px;"></div>
                    Отменен
                </span>
            </div>
        </div>        

        <div id="calendar"></div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="createBookingModal" tabindex="-1" aria-labelledby="createBookingLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title">Создание брони</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
            <form id="createBookingForm">
                <input type="hidden" name="rate_id" id="modalRateId">
                <input type="hidden" name="room_id" id="modalRoomId">
                    @csrf

                <div class="mb-3">
                <label for="modalDateRange" class="form-label">Диапазон дат</label>
                <input type="text" class="form-control" id="modalDateRange" name="daterange" required>
                </div>
    
                <div class="mb-3">
                <label for="modalAllotment" class="form-label">Квота</label>
                <input type="number" class="form-control" id="modalAllotment" name="allotment" min="1" value="1" required>
                </div>
    
                <button type="submit" class="btn btn-primary">Создать</button>
            </form>
            </div>
        </div>
        </div>
    </div>
  
@endsection

<style>
    #calendar{
        background-color: #fff;
        padding: 30px;
    }
    .fc-datagrid-cell-main {
        white-space: pre-line;
    }
    .fc-datagrid-cell-main b {
        margin-bottom: 5px;
        display: inline-block;
    }
    .fc-h-event .fc-event-main-frame{
        flex-direction: column
    }
    .fc-event-main{
        padding: 5px;
    }
    .fc-datagrid-cell-main{
        display: inline-block;
    }
</style>

<script>
    window.resourcesData = @json($resources);
    window.eventsData = @json($events);
</script>

{{-- @livewireScripts --}}