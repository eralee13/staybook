@extends('layouts.master')
@vite(['resources/css/app.css', 'resources/js/bookcalendar.js'])
{{-- @livewireStyles --}}

@section('content')
    <div class="container-fluid mt-5  mb-5">
        {{-- {{ print_r($resources) }} --}}
        {{-- {{ print_r($events) }} --}}
        <div class="status-container" style="display: flex; justify-content: space-around; align-items: center;">
            <div class="search">
                <form action="">
                    <div class="form-group">
                        <label for="">@lang('main.search-date')</label>
                        <input type="text" id="daterange" class="da" autocomplete="off" placeholder="Выберите дату" style="width: auto">
                        <button type="submit" class="btn btn-primary more" style="margin-left: 10px;">@lang('main.search')</button>
                    </div>
                    @php
                        use Carbon\Carbon;

                        // Добавляем 1 месяц вперёд и устанавливаем на 1 и 2 число
                        $startDate = Carbon::now()->addMonthNoOverflow()->startOfMonth()->format('Y-m-d');
                        $endDate = Carbon::now()->addMonthNoOverflow()->endOfMonth()->format('Y-m-d');


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
                    <div class="status-color" style="background-color: #0000ff; width: 15px; height: 15px; margin-right: 5px;"></div>
                    Отменен
                </span>
            </div>
        </div>        

        <div id="calendar"></div>
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
    .fc-view-harness, .fc-scroller {
        overflow: visible !important;
    }
    .fc-event-main{
        padding: 5px;
    }
</style>

<script>
    window.resourcesData = @json($resources);
    window.eventsData = @json($events);
</script>

{{-- @livewireScripts --}}