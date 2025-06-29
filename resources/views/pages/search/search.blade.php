@php use Carbon\Carbon;use Illuminate\Support\Facades\Http; @endphp
@extends('layouts.filter_mini')

@section('title', 'Поиск')

@section('content')

    @auth
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

        <style>
            .select2-container--default .select2-selection--single{
                height: 50px;
                line-height: 50px;
                display: block;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered{
                line-height: 50px;
            }
        </style>
        <div class="main-filter" style="padding-bottom: 40px">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ route('search') }}">
                            <div class="row">
                                <div class="col-lg-3 col-md-12">
                                    <div class="form-group">
                                        <div class="label stay"><img src="{{ route('index') }}/img/marker_out.svg"
                                                                     alt="">
                                        </div>
                                        <select name="city" id="city" required>
                                            <option value="{{ $request->city }}">{{ $request->city }}</option>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->title }}">{{ $city->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg col-6">
                                    <div class="form-group">
                                        <div class="label in"><img src="{{ route('index') }}/img/marker_in.svg" alt="">
                                            Заезд
                                        </div>
                                        <input type="text" id="date" class="date" required="">
                                        <input type="hidden" id="arrivalDate" name="arrivalDate"
                                               value="{{ $request->arrivalDate }}">
                                        <input type="hidden" id="departureDate" name="departureDate"
                                               value="{{ $request->departureDate }}">
                                    </div>
                                </div>
                                <div class="col-lg col-6">
                                    @php
                                        // 1) Берём массив комнат из запроса (если нет – пустой массив)
                                        $roomsData = $request->input('rooms', []);

                                        // 2) Сразу подсчитываем общее кол-во комнат, взрослых и детей
                                        $roomCount     = count($roomsData);
                                        $totalAdults   = 0;
                                        $totalChildren = 0;

                                        foreach ($roomsData as $r) {
                                            $totalAdults += (int) ($r['adults'] ?? 0);
                                            $totalChildren += count($r['childAges'] ?? []);
                                        }

                                        // 3) Готовим JSON для передачи в JS (чтобы JS сразу знал структуру rooms)
                                        $roomsJson = json_encode($roomsData, JSON_UNESCAPED_UNICODE);
                                    @endphp
                                    {{-- Фильтр комнат --}}
                                    {{-- Общая сводка (клик открывает окно) --}}
                                    <a href="javascript:void(0)"
                                       id="rooms-summary">
                                        Номера: {{ $roomCount }}, Взрослых: {{ $totalAdults }},
                                        Детей: {{ $totalChildren }}
                                    </a>

                                    {{-- Полупрозрачный оверлей --}}
                                    <div id="rooms-panel-overlay"></div>

                                    {{-- Окно снизу --}}
                                    <div id="rooms-panel">
                                            <div class="flex">
                                                <h3 class="text-lg font-medium">Гости и номера</h3>
                                                <div class="close-btn">
                                                    <a href="javascript:void(0)"
                                                       id="panel-close"
                                                       class="text-gray-500 hover:text-gray-700 text-xl">&times;</a>
                                                </div>
                                            </div>

                                            {{-- Кнопка добавить комнату --}}
                                            <div class="add-btn">
                                                <a href="javascript:void(0)"
                                                   id="add-room"
                                                   class="inline-block text-blue-600 hover:underline text-sm mb-4">
                                                    + @lang('main.add_room')
                                                </a>
                                            </div>

                                            {{-- Сюда будут рендериться комнаты --}}
                                            <div id="rooms-container" class="space-y-4"></div>

                                            <div class="mt-4 text-right">
                                                <button id="panel-apply" class="more">
                                                    @lang('main.ready')
                                                </button>
                                            </div>
                                    </div>

                                    {{-- Шаблон одной комнаты --}}
                                    <template id="room-template">
                                        <div class="guest-room"
                                             data-index="__INDEX__">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h4 class="flex justify-between items-center text-sm font-medium mb-3">
                                                        <span class="room-number">__NUM__</span> @lang('main.room')
                                                    </h4>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="remove-btn">
                                                        <a href="javascript:void(0)"
                                                           class="remove-room text-red-500 hover:text-red-700 text-xs ml-2">
                                                            @lang('main.delete')
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- скрытое поле для взрослых --}}
                                            <input type="hidden"
                                                   name="rooms[__INDEX__][adults]"
                                                   value="1"
                                                   class="input-adults">

                                            {{-- сводка по комнате --}}
                                            <a href="javascript:void(0)"
                                               class="guest-summary flex justify-between items-center w-full border border-gray-300
              rounded-md px-4 py-2 bg-white text-sm hover:border-blue-500">
                                                <span class="summary-text">1 @lang('main.adult')</span>
                                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </a>

                                            {{-- дропдаун --}}
                                            <div class="guest-dropdown hidden absolute z-20 mt-1 w-full bg-white border border-gray-200
                rounded-md shadow-lg p-4">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <span class="text-sm">@lang('main.count_adult')</span>
                                                        <div class="flex items-center">
                                                            <button class="dec-adult">−</button>
                                                            <span class="count-adult mx-3 w-5 text-center text-sm">1</span>
                                                            <button class="inc-adult">+</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="flex justify-between items-center mb-4">
                                                            <span class="text-sm">@lang('main.count_child')</span>
                                                            <div class="flex items-center">
                                                                <button class="dec-child">−</button>
                                                                <span class="count-child mx-3 w-5 text-center text-sm">0</span>
                                                                <button class="inc-child">+</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="children-ages space-y-2 mb-4"></div>
                                                <div class="text-right">
                                                    <button class="apply-guests inline-block bg-blue-600 hover:bg-blue-700 text-white
                       rounded-md px-4 py-2 text-sm">@lang('main.apply')
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', () => {
                                            const MAX_ROOMS      = 4;
                                            const summaryBtn     = document.getElementById('rooms-summary');
                                            const overlay        = document.getElementById('rooms-panel-overlay');
                                            const panel          = document.getElementById('rooms-panel');
                                            const closeBtn       = document.getElementById('panel-close');
                                            const applyBtn       = document.getElementById('panel-apply');
                                            const addRoomBtn     = document.getElementById('add-room');
                                            const roomsContainer = document.getElementById('rooms-container');
                                            const tplHtml        = document.getElementById('room-template').innerHTML;
                                            let nextIndex        = 0;

                                            // данные из PHP
                                            const initialRooms = {!! $roomsJson !!};

                                            // Открыть/закрыть
                                            function openPanel() {
                                                overlay.classList.add('open');
                                                panel.classList.add('open');
                                            }
                                            function closePanel() {
                                                overlay.classList.remove('open');
                                                panel.classList.remove('open');
                                            }

                                            // Глобальная сводка
                                            function updateGlobalSummary() {
                                                const rooms = roomsContainer.querySelectorAll('.guest-room');
                                                let adults=0, children=0;
                                                rooms.forEach(r => {
                                                    adults  += +r.querySelector('.count-adult').textContent;
                                                    children+= +r.querySelector('.count-child').textContent;
                                                });
                                                summaryBtn.textContent =
                                                    `Номера: ${rooms.length}, Взрослых: ${adults}, Детей: ${children}`;
                                                // кнопка добавить
                                                addRoomBtn.style.display = rooms.length < MAX_ROOMS ? 'inline-block' : 'none';
                                            }

                                            // Переиндексация комнат
                                            function reindexRooms() {
                                                roomsContainer.querySelectorAll('.guest-room').forEach((r,i) => {
                                                    r.dataset.index = i;
                                                    r.querySelector('.room-number').textContent = i+1;
                                                    r.querySelector('.input-adults').name = `rooms[${i}][adults]`;
                                                    r.querySelectorAll('.children-ages select').forEach((sel,ci)=>{
                                                        sel.name = `rooms[${i}][childAges][${ci}]`;
                                                    });
                                                });
                                                updateGlobalSummary();
                                            }

                                            // Обновить сводку одной комнаты
                                            function updateRoomSummary(roomEl) {
                                                const a = +roomEl.querySelector('.count-adult').textContent;
                                                const c = +roomEl.querySelector('.count-child').textContent;
                                                const txt = [`${a} ${a===1?'взрослый':'взрослых'}`];
                                                if(c) txt.push(`${c} ${c===1?'ребёнок':'детей'}`);
                                                roomEl.querySelector('.summary-text').textContent = txt.join(', ');
                                                roomEl.querySelector('.input-adults').value = a;
                                                updateGlobalSummary();
                                            }

                                            // Инициализация одной комнаты (навешиваем события)
                                            function initRoom(roomEl) {
                                                // кнопка сводки
                                                roomEl.querySelector('.guest-summary').addEventListener('click', e=>{
                                                    e.preventDefault();
                                                    roomEl.querySelector('.guest-dropdown').classList.toggle('hidden');
                                                });
                                                // dec/inc взрослых и детей, удаление, применить
                                                roomEl.addEventListener('click', e=>{
                                                    const tgt = e.target;
                                                    if (tgt.classList.contains('dec-adult')||tgt.classList.contains('inc-adult')) {
                                                        e.preventDefault();
                                                        const cnt = roomEl.querySelector('.count-adult');
                                                        let v = +cnt.textContent;
                                                        if (tgt.classList.contains('dec-adult') && v>1) v--;
                                                        if (tgt.classList.contains('inc-adult') && v<8) v++;
                                                        cnt.textContent = v;
                                                        updateRoomSummary(roomEl);
                                                    }
                                                    if (tgt.classList.contains('dec-child')||tgt.classList.contains('inc-child')) {
                                                        e.preventDefault();
                                                        const cnt = roomEl.querySelector('.count-child');
                                                        let v = +cnt.textContent;
                                                        if (tgt.classList.contains('dec-child')&&v>0) {
                                                            // удалить последний select
                                                            const wrap = roomEl.querySelector('.children-ages');
                                                            wrap.lastElementChild && wrap.removeChild(wrap.lastElementChild);
                                                            v--;
                                                        }
                                                        if (tgt.classList.contains('inc-child')&&v<3) {
                                                            const wrap = roomEl.querySelector('.children-ages');
                                                            // создать селект
                                                            const div = document.createElement('div');
                                                            div.className = 'flex items-center mb-2';
                                                            div.innerHTML = `<span class="mr-2 text-sm">@lang('main.age')</span>`;
                                                            const sel = document.createElement('select');
                                                            sel.className = 'border rounded px-2 py-1 text-sm';
                                                            for(let age=0; age<=18; age++){
                                                                sel.insertAdjacentHTML('beforeend', `<option value="${age}">${age}</option>`);
                                                            }
                                                            div.appendChild(sel);
                                                            wrap.appendChild(div);
                                                            v++;
                                                        }
                                                        cnt.textContent = v;
                                                        updateRoomSummary(roomEl);
                                                        reindexRooms();
                                                    }
                                                    if (tgt.classList.contains('remove-room')) {
                                                        e.preventDefault();
                                                        roomEl.remove();
                                                        reindexRooms();
                                                    }
                                                    if (tgt.classList.contains('apply-guests')) {
                                                        e.preventDefault();
                                                        roomEl.querySelector('.guest-dropdown').classList.add('hidden');
                                                    }
                                                });
                                            }

                                            // Добавление комнаты: data – {adults, childAges[]} или undefined
                                            function addRoom(data) {
                                                if (roomsContainer.children.length >= MAX_ROOMS) return;
                                                const idx = nextIndex++;
                                                const num = roomsContainer.children.length + 1;
                                                const html = tplHtml.replace(/__INDEX__/g,idx).replace(/__NUM__/g,num);
                                                roomsContainer.insertAdjacentHTML('beforeend', html);
                                                const newRoom = roomsContainer.querySelector(`.guest-room[data-index="${idx}"]`);
                                                initRoom(newRoom);
                                                // если data – подставляем значения
                                                if (data) {
                                                    newRoom.querySelector('.count-adult').textContent = data.adults||1;
                                                    newRoom.querySelector('.count-child').textContent = (data.childAges||[]).length;
                                                    const wrap = newRoom.querySelector('.children-ages');
                                                    wrap.innerHTML = '';
                                                    (data.childAges||[]).forEach(age=>{
                                                        const div = document.createElement('div');
                                                        div.className = 'flex items-center mb-2';
                                                        div.innerHTML = `<span class="mr-2 text-sm">@lang('main.age')</span>`;
                                                        const sel = document.createElement('select');
                                                        sel.className = 'border rounded px-2 py-1 text-sm';
                                                        for(let a=0;a<=18;a++){
                                                            sel.insertAdjacentHTML('beforeend',`<option value="${a}"${a==age?' selected':''}>${a}</option>`);
                                                        }
                                                        div.appendChild(sel);
                                                        wrap.appendChild(div);
                                                    });
                                                }
                                                updateRoomSummary(newRoom);
                                                reindexRooms();
                                            }

                                            // События открытия/закрытия
                                            summaryBtn.addEventListener('click', e=>{ e.preventDefault(); openPanel(); });
                                            closeBtn  .addEventListener('click', e=>{ e.preventDefault(); closePanel(); });
                                            applyBtn  .addEventListener('click', e=>{ e.preventDefault(); closePanel(); });
                                            overlay   .addEventListener('click',     closePanel);
                                            addRoomBtn.addEventListener('click', e=>{ e.preventDefault(); addRoom(); });

                                            // При инициализации: если есть из запроса — рендерим их, иначе одну
                                            if (initialRooms && initialRooms.length) {
                                                initialRooms.forEach(roomData => addRoom(roomData));
                                            } else {
                                                addRoom();
                                            }
                                        });
                                    </script>



                                </div>
                                <div class="col-lg col-6 extra">
                                    <div class="form-group">
                                        <div id="filter">
                                            <div class="label filter"><img src="{{route('index')}}/img/setting.svg"
                                                                           alt="">
                                                @lang('main.filters')
                                            </div>
                                            <div class="filter-wrap" id="filter-wrap">
                                                <div class="closebtn" id="closebtn"><img
                                                            src="{{route('index')}}/img/close.svg" alt=""></div>
                                                <h5>@lang('main.filters')</h5>
                                                <div class="form-group">
                                                    <div class="name">@lang('main.rating')</div>
                                                    <div class="row justify-content-center">
                                                        <div class="col-lg col-md-4">
                                                            <div class="item">
                                                                <input type="radio" id="1" name="rating" value="1"
                                                                       @if($request->rating == 1) checked @endif>
                                                                <div class="img @if($request->rating == 1) active @endif">
                                                                    <label for="1">1</label>
                                                                    <div class="img-wrap">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="2" id="2"
                                                                       @if($request->rating == 2) checked @endif>
                                                                <div class="img @if($request->rating == 2) active @endif">
                                                                    <label for="2">2</label>
                                                                    <div class="img-wrap">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="3" id="3"
                                                                       @if($request->rating == 3) checked @endif>
                                                                <div class="img @if($request->rating == 3) active @endif">
                                                                    <label for="3">3</label>
                                                                    <div class="img-wrap">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="4" id="4"
                                                                       @if($request->rating == 4) checked @endif>
                                                                <div class="img @if($request->rating == 4) active @endif">
                                                                    <label for="4">4</label>
                                                                    <div class="img-wrap">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="5" id="5"
                                                                       @if($request->rating == 5) checked @endif>
                                                                <div class="img @if($request->rating == 5) active @endif">
                                                                    <label for="5">5</label>
                                                                    <div class="img-wrap">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="line"></div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <div class="apart-item">
                                                                <img src="{{route('index')}}/img/hotelb.svg" alt="">
                                                                <h6>@lang('main.hotels')</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="line"></div>
                                                <div class="name">@lang('main.arrival')</div>
                                                <div class="form-group" id="income">
                                                    <div class="row">
                                                        <div class="col-md-6 col-6">
                                                            <div class="itemm">
                                                                <input type="checkbox" value="early_in">
                                                                <label for="">@lang('main.early_in')</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-6">
                                                            <div class="itemm">
                                                                <input type="checkbox" value="late_out">
                                                                <label for="">@lang('main.late_out')</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="line"></div>
                                                <div class="form-group" id="meal">
                                                    <div class="name">@lang('main.meal_plans')</div>
                                                    <div class="row">
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm @if($request->meal_id == 1) active @endif">
                                                                <input type="radio" value="1" id="ro" name="meal_id"
                                                                       @if($request->meal_id == 1) checked @endif">
                                                                <label for="ro">RO</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm @if($request->meal_id == 2) active @endif">
                                                                <input type="radio" value="2" id="bb" name="meal_id"
                                                                       @if($request->meal_id == 2) checked @endif>
                                                                <label for="bb">BB</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm @if($request->meal_id == 3) active @endif">
                                                                <input type="radio" id="hb" value="3" name="meal_id"
                                                                       @if($request->meal_id == 3) checked @endif>
                                                                <label for="hb">HB</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm @if($request->meal_id == 4) active @endif">
                                                                <input type="radio" id="fb" value="4" name="meal_id"
                                                                       @if($request->meal_id == 4) checked @endif>
                                                                <label for="fb">FB</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm @if($request->meal_id == 5) active @endif">
                                                                <input type="radio" id="ai" value="5" name="meal_id"
                                                                       @if($request->meal_id == 5) checked @endif>
                                                                <label for="ai">AI</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button class="more">@lang('main.find')</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg col-12">
                                    <div class="form-group">
                                        <button class="more"><img src="{{ route('index') }}/img/search.svg" alt="">
                                            @lang('main.find')
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="page search">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        @if(isset($error))
                            <div class="alert alert-danger">
                                {{ $error }}
                            </div>
                            <div class="btn-wrap">
                                <a href="{{ route('index') }}" class="more">Попробуйте снова</a>
                            </div>
                        @endif
                    @if($results != null)
                            @if(isset($results->errors))
                                @foreach ($results->errors as $error)
                                    <div class="alert alert-danger">
                                        <h5>{{ $error->code }}</h5>
                                        <p style="margin-bottom: 0">{{ $error->message }}</p>
                                    </div>
                                @endforeach
                            @else
                                @if( isset($results->hotels))
                                    @foreach($results->hotels as $hotel)
                                        @php
                                            $amenities = explode(',', $hotel->amenities ?? '');
                                            $amenities = array_slice($amenities, 0, 8);
                                            $rooms = $request->input('rooms', []); // если нет — пустой массив
                                            $totalAdults    = 0;
                                            $allChildAges   = [];
                                            $roomCount=0;
                                            $child = 0;

                                            foreach ($rooms as $room) {
                                                $roomCount++;
                                                // Взрослые
                                                $totalAdults += (int) ($room['adults'] ?? 0);

                                                // Возрасты детей (если есть) собираем в единый массив
                                                if (!empty($room['childAges']) && is_array($room['childAges'])) {
                                                    foreach ($room['childAges'] as $age) {
                                                        $allChildAges[] = (int) $age;
                                                        $child++;
                                                    }
                                                }
                                            }

                                            $iconMap = [
                                                'wi-fi'             => 'wifi.svg',
                                                'интернет'          => 'wifi.svg',
                                                'Доступ в интернет' => 'wifi.svg',
                                                'чайный набор'      => 'tea.svg',
                                                'Питание включено'  => 'meal.svg',
                                                'минеральная вода'  => 'water.svg',
                                                'сауна'             => 'sauna.svg',
                                                'сейф'              => 'safe.svg',
                                                'Двуспальная кровать' => 'bed2.svg',
                                                'Гладильные принадлежности' => 'iron.svg',
                                                'Ванная комната' => 'bath.svg',
                                                'Сауна' => 'sauna.svg',
                                                'Сейф' => 'safe.svg',
                                                'Минибар' => 'minibar.svg',
                                                'Кондиционер' => 'cond.svg',
                                                'Туалетные принадлежности' => 'toilet.svg',
                                                'Душ' => 'shower.svg',
                                                'Звукоизоляция' => 'sound.svg',
                                                'Фен' => 'dry.svg',
                                                'Постельное бельё' => 'bed_sheets.svg',
                                                'Халат' => 'robe.svg',
                                                'Шкаф' => 'close.svg',
                                                'Телефон' => 'phone_hotel.svg',
                                                'Отопление' => 'heating.svg',
                                                'Письменный стол' => 'table.svg',
                                                'Минеральная вода' => 'water.svg',
                                            ];
                                            
                                        @endphp
                                        <div class="search-item">
                                            <div class="row">
                                                <div class="col-md-5 order-xl-1 order-lg-1 order-1">
                                                    <div class="img-wrap">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="main">
                                                                    @if( isset($hotel->images[0]->image) )
                                                                        <img src="{{ Storage::url($hotel->images[0]->image) }}" alt="">
                                                                    @else
                                                                        <img src="{{ asset('img/no-image.png') }}" alt="">
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="primary">
                                                                    @if( isset($hotel->images[1]->image) )
                                                                        <img src="{{ Storage::url($hotel->images[1]->image) }}" alt="">
                                                                    @else
                                                                        <img src="{{ asset('img/no-image.png') }}" alt="">
                                                                    @endif
                                                                </div>
                                                                <div class="primary">
                                                                    @if( isset($hotel->images[2]->image) )
                                                                        <img src="{{ Storage::url($hotel->images[2]->image) }}" alt="">
                                                                    @else
                                                                        <img src="{{ asset('img/no-image.png') }}" alt="">
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-5 order-xl-2 order-lg-2 order-3">
                                                    <h4>
                                                        {{ $hotel->title_en }} 
                                                        &#9733; {{ $hotel->rating }}
                                                    </h4>
                                                    <div class="amenities">
                                                        @foreach($amenities as $amenity)
                                                            @php
                                                                $iconFile = 'check.svg';
                                                                foreach ($iconMap as $keyword => $filename) {
                                                                    if (mb_stripos($amenity, $keyword) !== false) {
                                                                        $iconFile = $filename;
                                                                        break;
                                                                    }
                                                                }
                                                            @endphp
                                                            <div class="amenities-item">
                                                                <img src="{{ asset('img/icons/' . $iconFile) }}"
                                                                     alt="{{ $amenity }}">
                                                                <div class="name">{{ $amenity }}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="btn-wrap">
                                                        @if( $hotel->apiName == 'TM')

                                                            <div class="btn-wrap">
                                                                <form action="{{ route('hotel_tm', ["hid" => $hotel->hid]) }}">
                                                                    <input type="hidden" name="arrivalDate"
                                                                        value="{{ $request->arrivalDate }}">
                                                                    <input type="hidden" name="departureDate"
                                                                        value="{{ $request->departureDate }}">
                                                                    <input type="hidden" name="adult" value="{{ $totalAdults }}">
                                                                    <input type="hidden" name="child" value="{{ $child }}">
                                                                    <input type="hidden" name="roomCount" value="{{ $roomCount }}">
                                                                    
                                                                    @if($allChildAges)
                                                                        @foreach($allChildAges as $age)
                                                                            <input type="hidden" name="childAges[]"
                                                                                value="{{ $age }}">
                                                                        @endforeach
                                                                    @endif
                                                                    
                                                                    <input type="hidden" name="city" value="{{ $request->city }}">
                                                                    <input type="hidden" name="meal_id" value="{{       $request->meal_id }}">
                                                                    <input type="hidden" name="api_name"
                                                                        value="{{ $hotel->apiName ?? '' }}">
                                                    
                                                                    <button class="more">Показать все номера</button>
                                                                </form>
                                                            </div>

                                                        @elseif( $hotel->apiName == 'ETG' )
                                                            <div class="btn-wrap">
                                                                <form action="{{ route('hotel_etg', ["hid" => $hotel->hid]) }}">
                                                                    <input type="hidden" name="arrivalDate"
                                                                        value="{{ $request->arrivalDate }}">
                                                                    <input type="hidden" name="departureDate"
                                                                        value="{{ $request->departureDate }}">

                                                                    @foreach ($rooms as $i => $room)
                                                                        <input type="hidden" name="rooms[{{ $i }}][adults]" value="{{ $room['adults'] }}">
                                                                        
                                                                        @if (isset($room['childAges']))
                                                                            @foreach ($room['childAges'] as $a => $age)
                                                                                <input type="hidden" name="rooms[{{ $i }}][childAges][]" value="{{ $age }}">
                                                                            @endforeach
                                                                        @endif
                                                                    @endforeach
                                                                    
                                                                    <input type="hidden" name="city" value="{{ $request->city }}">
                                                                    <input type="hidden" name="meal_id" value="{{       $request->meal_id }}">
                                                                    <input type="hidden" name="apiHotelId"
                                                                        value="{{ $hotel->apiHotelId ?? '' }}">
                                                    
                                                                    <button class="more">Показать все номера</button>
                                                                </form>
                                                            </div>
                                                        @elseif( $hotel->apiName == 'Exely' )
                                                            {{-- Exely markup --}}
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-md-2 order-xl-3 order-lg-3 order-2">
                                                    <div class="price">от {{$hotel->totalPrice}} {{$hotel->currency}}</div>
                                                    <div class="night">ночь</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- @foreach($results->roomStays as $room)
                                        @php
                                            $hotel = \App\Models\Hotel::where('exely_id', $room->propertyId)->get()->first();
                                            $roomf = \App\Models\Room::where('hotel_id', $hotel->exely_id)->get()->first();
                                            $amenities = explode(',', $roomf->amenities);
                                            $items  = array_slice($amenities, 0, 8);
                                            $iconMap = [
                                                'wi-fi'             => 'wifi.svg',
                                                'интернет'          => 'wifi.svg',
                                                'Доступ в интернет' => 'wifi.svg',
                                                'чайный набор'      => 'tea.svg',
                                                'Питание включено'  => 'meal.svg',
                                                'минеральная вода'  => 'water.svg',
                                                'сауна'             => 'sauna.svg',
                                                'сейф'              => 'safe.svg',
                                                'Двуспальная кровать' => 'bed2.svg',
                                                'Гладильные принадлежности' => 'iron.svg',
                                                'Ванная комната' => 'bath.svg',
                                                'Сауна' => 'sauna.svg',
                                                'Сейф' => 'safe.svg',
                                                'Минибар' => 'minibar.svg',
                                                'Кондиционер' => 'cond.svg',
                                                'Туалетные принадлежности' => 'toilet.svg',
                                                'Душ' => 'shower.svg',
                                                'Звукоизоляция' => 'sound.svg',
                                                'Фен' => 'dry.svg',
                                                'Постельное бельё' => 'bed_sheets.svg',
                                                'Халат' => 'robe.svg',
                                                'Шкаф' => 'closet.svg',
                                                'шкаф для одежды' => 'closet.svg',
                                                'Телефон' => 'phone_hotel.svg',
                                                'Отопление' => 'heating.svg',
                                                'Письменный стол' => 'table.svg',
                                                'Минеральная вода' => 'water.svg',
                                            ];
                                        @endphp
                                        <div class="search-item">
                                            <div class="row">
                                                <div class="col-md-5 order-xl-1 order-lg-1 order-1">
                                                    <div class="img-wrap">
                                                        @if($hotel->image)
                                                            <img src="{{ Storage::url($hotel->image) }}" alt="">
                                                        @else
                                                            <img src="{{ route('index')}}/img/noimage.png" alt="">
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-5 order-xl-2 order-lg-2 order-3">
                                                    <h4>{{ $hotel->title }}</h4>
                                                    <div class="amenities">
                                                        @foreach($items as $amenity)
                                                            @php
                                                                $iconFile = 'check.svg';
                                                                foreach ($iconMap as $keyword => $filename) {
                                                                    if (mb_stripos($amenity, $keyword) !== false) {
                                                                        $iconFile = $filename;
                                                                        break;
                                                                    }
                                                                }
                                                            @endphp
                                                            <div class="amenities-item">
                                                                <img src="{{ asset('img/icons/' . $iconFile) }}"
                                                                     alt="{{ $amenity }}">
                                                                <div class="name">{{ $amenity }}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="btn-wrap">
                                                        <div class="btn-wrap">
                                                            <form action="{{ route('hotel_exely', $room->roomType->id) }}">
                                                                <input type="hidden" name="propertyId"
                                                                       value="{{ $room->propertyId }}">
                                                                <input type="hidden" name="arrivalDate"
                                                                       value="{{ $request->arrivalDate }}">
                                                                <input type="hidden" name="departureDate"
                                                                       value="{{ $request->departureDate }}">

                                                                <input type="hidden" name="adultCount"
                                                                       value="{{ $room->guestCount->adultCount }}">
                                                                @php
                                                                    $array_child = [];
                                                                @endphp
                                                                @foreach($room->guestCount->childAges as $child)
                                                                    @php
                                                                        $array_child[] = $child
                                                                    @endphp
                                                                @endforeach
                                                                <input type="hidden" name="childAges[]"
                                                                       value="{{ implode(', ', $array_child) }}">
                                                                <input type="hidden" name="ratePlanId"
                                                                       value="{{ $room->ratePlan->id }}">
                                                                <input type="hidden" name="roomTypeId"
                                                                       value="{{ $room->roomType->id }}">
                                                                @foreach($room->roomType->placements as $type)
                                                                    <input type="hidden" name="roomType"
                                                                           value="{{ $type->kind }}">
                                                                    <input type="hidden" name="roomCount"
                                                                           value="{{ $type->count }}">
                                                                    <input type="hidden" name="roomCode"
                                                                           value="{{ $type->code }}">
                                                                    <input type="hidden" name="minAge"
                                                                           value="{{ $type->minAge }}">
                                                                    <input type="hidden" name="maxAge"
                                                                           value="{{ $type->maxAge }}">
                                                                @endforeach

                                                                <input type="hidden" name="checkSum"
                                                                       value="{{ $room->checksum }}">
                                                                @foreach($room->includedServices as $serv)
                                                                    <input type="hidden" name="servicesId"
                                                                           value="{{ $serv->id }}">
                                                                @endforeach

                                                                <input type="hidden" name="hotel"
                                                                       value="{{ $room->fullPlacementsName }}">
                                                                <input type="hidden" name="hotel_id"
                                                                       value="{{ $room->propertyId }}">
                                                                <input type="hidden" name="room_id"
                                                                       value="{{ $room->roomType->id }}">
                                                                <input type="hidden" name="title"
                                                                       value="{{ $room->fullPlacementsName }}">
                                                                <input type="hidden" name="price"
<<<<<<< HEAD
                                                                       value="{{ round($room->total->priceBeforeTax * config('services.main.coef')/100 + $room->total->priceBeforeTax, 0) }}">
=======
                                                                       value="{{ $room->total->priceBeforeTax }}">
>>>>>>> origin/eralast
                                                                <button class="more">@lang('main.show_all_rooms')</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 order-xl-3 order-lg-3 order-2">
                                                    <div class="price">
<<<<<<< HEAD
                                                        @lang('main.from') {{ round($room->total->priceBeforeTax * config('services.main.coef')/100 + $room->total->priceBeforeTax, 0) }} {{ $room->currencyCode }}</div>
=======
                                                        @lang('main.from') {{ $room->total->priceBeforeTax }} {{ $room->currencyCode }}</div>
>>>>>>> origin/eralast
                                                    <div class="night">@lang('main.night')</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach --}}
                                @else
                                    <div class="alert alert-danger">@lang('main.search_not_found') <a href="{{ route('index') }}">@lang('main.try_again')</a></div>
                                @endif
                            @endif
                        @else
                            <!-- local hotels-->
                            @foreach($hotels as $hotel)
                                @php
                                    $items = \App\Models\Amenity::where('hotel_id', $hotel->id)->get()->first();
                                    $images = \App\Models\Image::where('hotel_id', $hotel->id)->get();
                                    $amenities = explode(',', $items->services);
                                    $items  = array_slice($amenities, 0, 8);
                                    $iconMap = [
                                        'wi-fi'             => 'wifi.svg',
                                        'интернет'          => 'wifi.svg',
                                        'Доступ в интернет' => 'wifi.svg',
                                        'чайный набор'      => 'tea.svg',
                                        'Питание включено'  => 'meal.svg',
                                        'минеральная вода'  => 'water.svg',
                                        'сауна'             => 'sauna.svg',
                                        'сейф'              => 'safe.svg',
                                        'Двуспальная кровать' => 'bed2.svg',
                                        'Гладильные принадлежности' => 'iron.svg',
                                        'Ванная комната' => 'bath.svg',
                                        'Сауна' => 'sauna.svg',
                                        'Сейф' => 'safe.svg',
                                        'Минибар' => 'minibar.svg',
                                        'Кондиционер' => 'cond.svg',
                                        'Туалетные принадлежности' => 'toilet.svg',
                                        'Душ' => 'shower.svg',
                                        'Звукоизоляция' => 'sound.svg',
                                        'Фен' => 'dry.svg',
                                        'Постельное бельё' => 'bed_sheets.svg',
                                        'Халат' => 'robe.svg',
                                        'Шкаф' => 'closet.svg',
                                        'шкаф для одежды' => 'closet.svg',
                                        'Телефон' => 'phone_hotel.svg',
                                        'Отопление' => 'heating.svg',
                                        'Письменный стол' => 'table.svg',
                                        'Минеральная вода' => 'water.svg',
                                    ];
                                @endphp
                                @php
                                    // Если в GET-параметрах нет childAges — будет пустой массив
                                    $childAges = $request->input('childAges', []);

                                    // Если пришла строка вида "1, 9", разбираем её в массив ['1', '9']
                                    if (is_string($childAges)) {
                                        $childAges = array_filter(
                                            array_map('trim', explode(',', $childAges)),
                                            fn($v) => $v !== ''
                                        );
                                    }

                                    // Убедимся, что теперь $childAges — именно массив (например [] или ['1','9'])
                                    if (! is_array($childAges)) {
                                        $childAges = [];
                                    }
                                @endphp
                                <div class="search-item">
                                    <div class="row">
                                        <div class="col-md-5 order-xl-1 order-lg-1 order-1">
                                            <div class="img-wrap">
                                                @if($images->isNotEmpty())
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="main">
                                                                <img src="{{ Storage::url($hotel->image) }}"
                                                                     alt="">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            @foreach($images as $file)
                                                                <div class="primary">
                                                                    <img src="{{ Storage::url($file->image) }}"
                                                                         alt="">
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <img src="{{ Storage::url($hotel->image) }}" alt="">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-5 order-xl-2 order-lg-2 order-3">
                                            <h4>{{ $hotel->__('title') }}</h4>
                                            <div class="amenities">
                                                @foreach($items as $amenity)
                                                    @php
                                                        $iconFile = 'check.svg';
                                                        foreach ($iconMap as $keyword => $filename) {
                                                            if (mb_stripos($amenity, $keyword) !== false) {
                                                                $iconFile = $filename;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="amenities-item">
                                                        <img src="{{ asset('img/icons/' . $iconFile) }}"
                                                             alt="{{ $amenity }}">
                                                        <div class="name">{{ $amenity }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="btn-wrap">
                                                <div class="btn-wrap">
                                                    <form action="{{ route('hotel', $hotel->code) }}">
                                                        <input type="hidden" name="arrivalDate"
                                                               value="{{ $request->arrivalDate }}">
                                                        <input type="hidden" name="departureDate"
                                                               value="{{ $request->departureDate }}">
                                                        @php
                                                            $totalAdults   = 0;
                                                            $totalChildren = 0;
                                                            if (!empty($request->rooms) && is_array($request->rooms)) {
                                                                foreach ($request->rooms as $room) {
                                                                    // Добавляем взрослых
                                                                    $totalAdults += (int) ($room['adults'] ?? 0);

                                                                    // Считаем детей в этой комнате
                                                                    $totalChildren += isset($room['childAges']) && is_array($room['childAges'])
                                                                                      ? count($room['childAges'])
                                                                                      : 0;
                                                                }
                                                            }

                                                            // Вычисляем количество ночей
                                                $arr    = Carbon::parse($request->arrivalDate);
                                                $dep    = Carbon::parse($request->departureDate);
                                                $nights = $arr->diffInDays($dep);
                                                $totalPrice = 0;
                                                // Получаем самый дешевый тариф по отелю (например, для всех комнат одинаковый)
                                                $rate = \App\Models\Rate::where('hotel_id', $hotel->id)
                                                         ->orderBy('price', 'asc')
                                                         ->first();

                                                if ($rate) {
                                                    $сhildAges = [];

                                                        $price_child = 0;

                                                        if (!empty($request->rooms) && is_array($request->rooms)) {
                                                            foreach ($request->rooms as $room) {
                                                                // Если в этой комнате задан массив childAges — перебираем и добавляем
                                                                if (!empty($room['childAges']) && is_array($room['childAges'])) {
                                                                    foreach ($room['childAges'] as $age) {
                                                                        $childAges[] = $age;
                                                                        $age = (int) $age;
                                                                        if ($age >= $rate->free_children_age) {
                                                                            $price_child += $rate->child_extra_fee;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        // Вычисляем стоимость для этой комнаты:
                                                        // если взрослых >= 2, используем price2, иначе – price
                                                        if ($totalAdults >= 2) {
                                                            $price = ($rate->price2 + $price_child) * 1 * $nights;
                                                        } else {
                                                            $price = ($rate->price + $price_child) * 1 * $nights;
                                                        }
                                                }
                                                        @endphp
                                                        <input type="hidden" name="adult" value="{{ $totalAdults }}">
                                                        <input type="hidden" name="child" value="{{ $totalChildren }}">
                                                        <input type="hidden" name="childAges[]"
                                                               value="{{ implode(', ', $childAges) }}">
                                                        <input type="hidden" name="meal_id"
                                                               value="{{ $request->meal_id }}">
                                                        <button class="more">@lang('main.show_all_rooms')</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-2 order-xl-3 order-lg-3 order-2">
                                            @php
                                                // 1) Исходная цена с коэффициентом
                                                $basePrice = round($price * config('services.main.coef') + $price);
                                                // 2) Курс для выбранной валюты (например, ['usd'=>1,'rub'=>…,'kgs'=>…])
                                                //    ключи fxRates – в нижнем регистре
                                                $rateKey = strtolower($fxBase);
                                                $currencyRate = $fxRates[$rateKey] ?? 1;
                                                // 3) Переводим в выбранную валюту
                                                //$converted = round($basePrice * $currencyRate);
                                                $symbols = ['USD' => '$', 'RUB' => '₽', 'KGS' => 'сом'];
                                                $symbol = $symbols[$fxBase] ?? $fxBase;
                                            @endphp
                                            <div class="price">@lang('main.from') {{ number_format($basePrice) }}
                                                {{ $symbol }}
                                            </div>
                                            <div class="night">@lang('main.night')</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>
        </div>

    @else
        @include('layouts.auth')
    @endauth

@endsection
