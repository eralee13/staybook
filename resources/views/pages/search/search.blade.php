@php
    use App\Models\Image;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.main')

@section('title', 'Поиск')

@section('content')
    @auth
        <div class="main-filter">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="type">
                            <div class="type-item current">
                                <a href="{{ route('index') }}">@lang('main.hotels_and_rooms')</a>
                            </div>
                            <div class="type-item">
                                <a href="{{ route('offline') }}">@lang('main.offline')</a>
                            </div>
                        </div>
                        <form action="{{ route('search') }}" method="GET">
                            <div class="row">
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <input type="text" id="searchbox" name="city" placeholder="@lang('main.city_or_hotel')"
                                               autocomplete="off" value="{{ $request->city }}" required>
                                        <div id="suggest" class="suggest hidden"></div>
                                        <input type="hidden" name="city_id" id="city_id">
                                        <style>
                                            .suggest {
                                                position: absolute;
                                                z-index: 9999;
                                                background: #fff;
                                                border: 1px solid #e5e7eb;
                                                width: 100%;
                                                max-height: 280px;
                                                overflow: auto;
                                                border-radius: 8px;
                                                box-shadow: 0 10px 20px rgba(0, 0, 0, .08)
                                            }

                                            .suggest.hidden {
                                                display: none
                                            }

                                            .suggest-item {
                                                padding: 10px 12px;
                                                cursor: pointer;
                                                display: flex;
                                                gap: 8px;
                                                align-items: center
                                            }

                                            .suggest-item:hover,
                                            .suggest-item.active {
                                                background: #f3f4f6
                                            }

                                            .s-title {
                                                font-weight: 600;
                                                font-size: 14px
                                            }

                                            .s-sub {
                                                font-size: 12px;
                                                color: #6b7280
                                            }

                                            .s-badge {
                                                font-size: 11px;
                                                color: #111827;
                                                background: #fef3c7;
                                                border: 1px solid #fcd34d;
                                                border-radius: 6px;
                                                padding: 2px 6px
                                            }
                                        </style>

                                        <script>
                                            document.addEventListener('DOMContentLoaded', () => {
                                                const input = document.getElementById('searchbox');
                                                const box = document.getElementById('suggest');
                                                const url = @json(route('suggest'));
                                                let items = [];
                                                let activeIdx = -1;
                                                let lastQuery = '';
                                                let t = null;

                                                function debounce(fn, ms) {
                                                    return (...args) => {
                                                        clearTimeout(t);
                                                        t = setTimeout(() => fn(...args), ms);
                                                    };
                                                }

                                                function hide() {
                                                    box.classList.add('hidden');
                                                    activeIdx = -1;
                                                }

                                                function show() {
                                                    box.classList.remove('hidden');
                                                }

                                                function render(list) {
                                                    if (!list.length) {
                                                        hide();
                                                        return;
                                                    }
                                                    box.innerHTML = list.map((it, i) => {
                                                        const rating = it.rating ? `<span class="s-badge">★ ${it.rating}</span>` : '';
                                                        const type = it.type === 'hotel' ?
                                                            '<span class="s-badge">Отель</span>' :
                                                            '<span class="s-badge">Город</span>';
                                                        const alt = it.alt && it.alt !== it.label ?
                                                            ` · <span class="s-sub">${it.alt}</span>` : '';
                                                        const city = it.city ? `<div class="s-sub">${it.city}</div>` : '';
                                                        return `<div class="suggest-item" data-idx="${i}">
  <div>
    <div class="s-title">${it.label} ${rating}${alt} ${type}</div>
    ${city}
  </div>
</div>`;
                                                    }).join('');
                                                    show();
                                                }

                                                async function fetchSuggest(q) {
                                                    try {
                                                        const resp = await fetch(url + '?q=' + encodeURIComponent(q), {
                                                            headers: {
                                                                'Accept': 'application/json'
                                                            },
                                                            cache: 'no-store',
                                                        });
                                                        const ct = resp.headers.get('content-type') || '';
                                                        if (!resp.ok) {
                                                            console.error('[suggest] HTTP', resp.status, await resp.text());
                                                            return hide();
                                                        }
                                                        if (!ct.includes('application/json')) {
                                                            console.error('[suggest] Not JSON, got:', ct, await resp.text());
                                                            return hide();
                                                        }
                                                        const data = await resp.json();
                                                        items = Array.isArray(data.items) ? data.items : [];
                                                        render(items);
                                                    } catch (e) {
                                                        console.error('[suggest] fetch error', e);
                                                        hide();
                                                    }
                                                }

                                                const onType = debounce((e) => {
                                                    const q = (e.target.value || '').trim();
                                                    if (q.length < 2) {
                                                        hide();
                                                        lastQuery = '';
                                                        return;
                                                    }
                                                    if (q === lastQuery) return;
                                                    lastQuery = q;
                                                    fetchSuggest(q);
                                                }, 250);

                                                input.addEventListener('input', onType);
                                                input.addEventListener('focus', () => {
                                                    if ((input.value || '').trim().length >= 2 && items.length) show();
                                                });
                                                input.addEventListener('blur', () => setTimeout(hide, 150));

                                                // Клик по подсказке
                                                box.addEventListener('click', (e) => {
                                                    const itemEl = e.target.closest('.suggest-item');
                                                    if (!itemEl) return;
                                                    const idx = +itemEl.dataset.idx;
                                                    const it = items[idx];
                                                    if (!it) return;

                                                    input.value = it.label;
                                                    if (it.city_id) {
                                                        const cityIdEl = document.getElementById('city_id');
                                                        if (cityIdEl) cityIdEl.value = it.city_id;
                                                    }
                                                    hide();
                                                    //if (it.url) window.location.href = it.url; // для отелей переход сразу
                                                });

                                                // Навигация стрелками и Enter
                                                input.addEventListener('keydown', (e) => {
                                                    if (box.classList.contains('hidden')) return;
                                                    if (e.key === 'ArrowDown') {
                                                        e.preventDefault();
                                                        activeIdx = (activeIdx + 1) % items.length;
                                                        highlight();
                                                    } else if (e.key === 'ArrowUp') {
                                                        e.preventDefault();
                                                        activeIdx = (activeIdx - 1 + items.length) % items.length;
                                                        highlight();
                                                    } else if (e.key === 'Enter') {
                                                        if (activeIdx >= 0 && items[activeIdx]) {
                                                            e.preventDefault();
                                                            const it = items[activeIdx];
                                                            input.value = it.label;
                                                            if (it.city_id) {
                                                                const cityIdEl = document.getElementById('city_id');
                                                                if (cityIdEl) cityIdEl.value = it.city_id;
                                                            }
                                                            hide();
                                                            if (it.url) window.location.href = it.url;
                                                        }
                                                    } else if (e.key === 'Escape') {
                                                        hide();
                                                    }
                                                });

                                                function highlight() {
                                                    [...box.querySelectorAll('.suggest-item')].forEach((el, i) => {
                                                        el.classList.toggle('active', i === activeIdx);
                                                        if (i === activeIdx) {
                                                            el.scrollIntoView({
                                                                block: 'nearest'
                                                            });
                                                        }
                                                    });
                                                }
                                            });
                                        </script>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <input type="text" id="arrivalDisplay" class="date" autocomplete="off"
                                               value="{{ Carbon::parse($request->arrivalDate)->format('d.m.Y')}}">
                                        <input type="hidden" id="arrivalDate" name="arrivalDate"
                                               value="{{ $request->arrivalDate }}">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <input type="text" id="departureDisplay" class="date" autocomplete="off"
                                               value="{{ Carbon::parse($request->departureDate)->format('d.m.Y')}}">
                                        <input type="hidden" id="departureDate" name="departureDate"
                                               value="{{ $request->departureDate }}">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        @php
                                            // 1) Берём массив комнат из запроса (если нет – пустой массив)
                                            $roomsData = $request->input('rooms', []);

                                            // 2) Сразу подсчитываем общее кол-во комнат, взрослых и детей
                                            $roomCount = count($roomsData);
                                            $totalAdults = 0;
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
                                            @lang('main.room'): 1, @lang('main.adult'): 1, @lang('main.child'): 0
                                        </a>

                                        {{-- Полупрозрачный оверлей --}}
                                        <div id="rooms-panel-overlay"
                                             class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

                                        <div id="rooms-panel">
                                            <div class="flex justify-between items-center">
                                                <h3 class="text-lg font-medium">@lang('main.guests_and_rooms')</h3>
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
                                                    @lang('main.add_room')
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
                                                              d="M19 9l-7 7-7-7" />
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
                                                        <button class="apply-guests">@lang('main.apply')
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <script>
                                            document.addEventListener('DOMContentLoaded', () => {
                                                const MAX_ROOMS = 4;
                                                const summaryBtn = document.getElementById('rooms-summary');
                                                const overlay = document.getElementById('rooms-panel-overlay');
                                                const panel = document.getElementById('rooms-panel');
                                                const closeBtn = document.getElementById('panel-close');
                                                const applyBtn = document.getElementById('panel-apply');
                                                const addRoomBtn = document.getElementById('add-room');
                                                const roomsContainer = document.getElementById('rooms-container');
                                                const tpl = document.getElementById('room-template').innerHTML;
                                                let nextIndex = 0;

                                                function openPanel() {
                                                    overlay.classList.remove('hidden');
                                                    panel.classList.add('open');
                                                }

                                                function closePanel() {
                                                    panel.classList.remove('open');
                                                    overlay.classList.add('hidden');
                                                }

                                                function updateGlobalSummary() {
                                                    const rooms = roomsContainer.querySelectorAll('.guest-room');
                                                    const roomCount = rooms.length;
                                                    let adultsTotal = 0;
                                                    let childrenTotal = 0;
                                                    rooms.forEach(r => {
                                                        adultsTotal += +r.querySelector('.count-adult').textContent;
                                                        childrenTotal += +r.querySelector('.count-child').textContent;
                                                    });
                                                    summaryBtn.textContent =
                                                        `@lang('main.room'): ${roomCount}, @lang('main.adult'): ${adultsTotal}, @lang('main.child'): ${childrenTotal}`;
                                                    summaryBtn.classList.toggle('opacity-50', roomCount >= MAX_ROOMS);
                                                    summaryBtn.classList.toggle('pointer-events-none', roomCount >= MAX_ROOMS);
                                                }

                                                function reindexRooms() {
                                                    roomsContainer.querySelectorAll('.guest-room').forEach((r, i) => {
                                                        r.dataset.index = i;
                                                        r.querySelector('.room-number').textContent = i + 1;
                                                        r.querySelector('.input-adults').name = `rooms[${i}][adults]`;
                                                        // корректим name для каждого селекта детей
                                                        r.querySelectorAll('.children-ages select').forEach((sel, ci) => {
                                                            sel.name = `rooms[${i}][childAges][${ci}]`;
                                                        });
                                                    });
                                                    updateGlobalSummary();
                                                }

                                                function addRoom() {
                                                    if (roomsContainer.children.length >= MAX_ROOMS) return;
                                                    const idx = nextIndex++;
                                                    const num = roomsContainer.children.length + 1;
                                                    roomsContainer.insertAdjacentHTML(
                                                        'beforeend',
                                                        tpl.replace(/__INDEX__/g, idx).replace(/__NUM__/g, num)
                                                    );
                                                    reindexRooms();
                                                }

                                                function updateRoomSummary(room) {
                                                    const aCount = +room.querySelector('.count-adult').textContent;
                                                    const cCount = +room.querySelector('.count-child').textContent;
                                                    const parts = [`${aCount} ${aCount === 1 ? '{{__('main.adult')}}' : '{{__('main.adult')}}'}`];
                                                    if (cCount) parts.push(`${cCount} ${cCount === 1 ? '{{__('main.child')}}' : '{{__('main.child')}}'}`);
                                                    room.querySelector('.summary-text').textContent = parts.join(', ');
                                                    room.querySelector('.input-adults').value = aCount;
                                                    updateGlobalSummary();
                                                }

                                                // Открытие/закрытие
                                                summaryBtn.addEventListener('click', e => {
                                                    e.preventDefault();
                                                    openPanel();
                                                });
                                                closeBtn.addEventListener('click', e => {
                                                    e.preventDefault();
                                                    closePanel();
                                                });
                                                applyBtn.addEventListener('click', e => {
                                                    e.preventDefault();
                                                    closePanel();
                                                });
                                                overlay.addEventListener('click', closePanel);

                                                // Добавить комнату
                                                addRoomBtn.addEventListener('click', e => {
                                                    e.preventDefault();
                                                    addRoom();
                                                });

                                                // Делегируем клики по документу
                                                document.addEventListener('click', e => {
                                                    // если событие не в панели — игнор
                                                    if (!e.target.closest('.guest-room') &&
                                                        !e.target.closest('#rooms-summary') &&
                                                        !e.target.closest('#add-room')) {
                                                        return;
                                                    }

                                                    const room = e.target.closest('.guest-room');

                                                    if (e.target.closest('.remove-room')) {
                                                        e.preventDefault();
                                                        room.remove();
                                                        reindexRooms();
                                                        return;
                                                    }
                                                    if (e.target.closest('.guest-summary')) {
                                                        e.preventDefault();
                                                        room.querySelector('.guest-dropdown').classList.toggle('hidden');
                                                        return;
                                                    }
                                                    if (e.target.closest('.dec-adult')) {
                                                        e.preventDefault();
                                                        const cnt = room.querySelector('.count-adult');
                                                        if (+cnt.textContent > 1) cnt.textContent = +cnt.textContent - 1;
                                                        updateRoomSummary(room);
                                                        return;
                                                    }
                                                    if (e.target.closest('.inc-adult')) {
                                                        e.preventDefault();
                                                        const cnt = room.querySelector('.count-adult');
                                                        if (+cnt.textContent < 8) cnt.textContent = +cnt.textContent + 1;
                                                        updateRoomSummary(room);
                                                        return;
                                                    }
                                                    if (e.target.closest('.dec-child')) {
                                                        e.preventDefault();
                                                        const cnt = room.querySelector('.count-child');
                                                        if (+cnt.textContent > 0) cnt.textContent = +cnt.textContent - 1;
                                                        // убираем последний селект
                                                        const wrap = room.querySelector('.children-ages');
                                                        if (wrap.lastElementChild) wrap.removeChild(wrap.lastElementChild);
                                                        reindexRooms();
                                                        updateRoomSummary(room);
                                                        return;
                                                    }
                                                    if (e.target.closest('.inc-child')) {
                                                        e.preventDefault();
                                                        const cnt = room.querySelector('.count-child');
                                                        if (+cnt.textContent < 3) {
                                                            cnt.textContent = +cnt.textContent + 1;
                                                            // создаём select для возраста
                                                            const wrap = room.querySelector('.children-ages');
                                                            const div = document.createElement('div');
                                                            div.className = 'flex items-center';
                                                            div.innerHTML = `<span class="mr-2 text-sm">@lang('main.age')</span>`;
                                                            const sel = document.createElement('select');
                                                            sel.className = 'border border-gray-300 rounded-md px-2 py-1 text-sm';
                                                            for (let a = 0; a <= 18; a++) sel.insertAdjacentHTML('beforeend', `<option value="${a}">${a}</option>`);
                                                            div.appendChild(sel);
                                                            wrap.appendChild(div);
                                                            reindexRooms();
                                                            updateRoomSummary(room);
                                                        }
                                                        return;
                                                    }
                                                    if (e.target.closest('.apply-guests')) {
                                                        e.preventDefault();
                                                        room.querySelector('.guest-dropdown').classList.add('hidden');
                                                    }
                                                    if (e.target.closest('#rooms-summary') && !room) {
                                                        // клик по сводке — уже обрабатывается выше
                                                    }
                                                });

                                                // Инициализация: первая комната
                                                addRoom();
                                            });
                                        </script>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 extra">
                                    <div class="form-group">
                                        <div id="filter">
                                            <div class="label filter">@lang('main.filters')</div>
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
                                                <div class="form-group">
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
                                                            @php
                                                                $meals = \App\Models\Meal::all();
                                                            @endphp
                                                            @foreach ($meals as $meal)
                                                                <div class="col-lg">
                                                                    <div class="itemmm {{ in_array($meal->id, (array) request('meal')) ? 'active' : '' }}">
                                                                        <input type="checkbox"
                                                                               name="meal[]"
                                                                               id="{{ $meal->code }}"
                                                                               value="{{ $meal->id }}"
                                                                                {{ in_array($meal->id, (array) request('meal')) ? 'checked' : '' }}>
                                                                        <label class="meal-checkbox"
                                                                               for="{{ $meal->code }}">{{ $meal->code }}</label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        @csrf
                                        <button type="submit" class="more">@lang('main.find')</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="page search" style="margin-bottom: 60px">
            <div class="container-fluid">
                <div class="row">
                    {{-- Листинг --}}
                    <div class="col-lg-7 col-md-12">
                        <div id="hotel-list">
                            @if($allHotels->isEmpty())
                                <div class="alert alert-danger">@lang('main.not_hotel')</div>
                                <div class="btn-wrap">
                                    <a href="{{ route('index') }}">@lang('main.try_again')</a>
                                </div>
                            @else
                                @foreach($allHotels as $item)
                                    {{-- LOCAL --}}
                                    @if($item['source'] === 'local')
                                        @php
                                            $hotel = $item['hotel'];
                                            $title = is_object($hotel) ? $hotel->title : ($hotel['title'] ?? '');
                                            $first_image = \App\Models\Image::where('hotel_id', $hotel->id)->first();
                                            $images = \App\Models\Image::where('hotel_id', $hotel->id)->take(2)->offset(1)->get();
                                            $amenityObj = \App\Models\Amenity::where('hotel_id', $hotel->id)->first();
                                            $amenities = $amenityObj && $amenityObj->services ? explode(',', $amenityObj->services) : [];
                                            $items = array_slice($amenities, 0, 8);
                                            // готовые поля конвертации из контроллера
                                            $converted = $item['conv_total'];
                                            $symbol    = $item['conv_symbol'];
                                        @endphp
                                        <div class="search-item"
                                             data-type="{{ $hotel->type ?? '' }}"
                                             data-id="{{ $hotel->id }}"
                                             data-title="{{ strtolower($title) }}"
                                             data-price="{{ $item['price'] }}">
                                            <div class="row">
                                                <div class="col-md-6 order-xl-1 order-lg-1 order-1">
                                                    <div class="img-wrap">
                                                        <div class="owl-carousel owl-slider">
                                                            <div class="slider-item">
                                                                @if($hotel->image)
                                                                    <img src="{{ Storage::url($hotel->image) }}" alt="">
                                                                @elseif($images)
                                                                    @foreach($images as $file)
                                                                        <div class="primary">
                                                                            <img src="{{ Storage::url($file->image) }}"
                                                                                 alt="">
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <img src="{{ route('index') }}/img/noimage.png"
                                                                         alt="">
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="text-wrap">
                                                            <div class="price">
                                                                @lang('main.from') {{ number_format((int)$converted, 0, '.', ' ') }} {{ $symbol }}
                                                            </div>
                                                            <div class="night">@lang('main.night')</div>
                                                            @if($hotel->rating)
                                                                <div class="rating">
                                                                    {{ $hotel->rating }} <img src="{{ route('index') }}/img/star.svg" alt="">
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 order-xl-2 order-lg-2 order-2">
                                                    <div class="wrap">
                                                        <h4>{{ $hotel->__('title') }}</h4>
                                                        <div class="amenities">
                                                            @foreach($items as $amenity)
                                                                <div class="amenities-item">
                                                                    <img src="{{ asset('img/icons/check.svg') }}"
                                                                         alt="{{ $amenity }}">
                                                                    <div class="name">{{ $amenity }}</div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="address">{{ $hotel->__('address') }}</div>
                                                        <div class="btn-wrap">
                                                            <form action="{{ route('findHotel', $hotel->code) }}">
                                                                <input type="hidden" name="arrivalDate"
                                                                       value="{{ $request->arrivalDate }}">
                                                                <input type="hidden" name="departureDate"
                                                                       value="{{ $request->departureDate }}">
                                                                {{-- при необходимости добавьте adult/child из вашего виджета --}}
                                                                <button class="more">@lang('main.show_all_rooms')</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- EXELY --}}
                                    @elseif($item['source'] === 'exely')
                                        @php
                                            $room  = $item['roomStay'];
                                            $hotel = $item['hotel'];
                                            $title = is_object($hotel) ? $hotel->title : ($hotel['title'] ?? '');
                                            $images = \App\Models\Image::where('hotel_id', $hotel?->id)->get();
                                            $amenities = isset($hotel) && $hotel->amenity ? explode(',', $hotel->amenity->services) : [];
                                            $items = array_slice($amenities, 0, 8);
                                            $converted = $item['conv_total'];
                                            $symbol    = $item['conv_symbol'];
                                        @endphp

                                        <div class="search-item"
                                             data-id="{{ $hotel?->id }}"
                                             data-type="{{ $hotel->type ?? '' }}"
                                             data-title="{{ strtolower($title) }}"
                                             data-price="{{ $item['price'] }}">
                                            <div class="row">
                                                <div class="col-md-6 order-xl-1 order-lg-1 order-1">
                                                    <div class="img-wrap">
                                                        <div class="owl-carousel owl-slider">
                                                            <div class="slider-item">
                                                                @if($hotel?->image)
                                                                    <img src="{{ Storage::url($hotel->image) }}" alt="">
                                                                @elseif($images->isNotEmpty())
                                                                    <img src="{{ Storage::url($images->first()->image) }}"
                                                                         alt="">
                                                                    @foreach($images->slice(1, 2) as $file)
                                                                        <div class="primary">
                                                                            <img src="{{ Storage::url($file->image) }}"
                                                                                 alt="">
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <img src="{{ route('index')}}/img/noimage.png"
                                                                         alt="">
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="text-wrap">
                                                            <div class="price">
                                                                @lang('main.from') {{ number_format((int)$converted, 0, '.', ' ') }} {{ $symbol }}
                                                            </div>
                                                            <div class="night">@lang('main.night')</div>
                                                            @if($hotel?->rating)
                                                                <div class="rating"><img
                                                                            src="{{ route('index') }}/img/star.svg"
                                                                            alt=""> {{ $hotel->rating }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 order-xl-2 order-lg-2 order-2">
                                                    <div class="wrap">
                                                        <h4>{{ $hotel?->title }}</h4>
                                                        <div class="amenities">
                                                            @foreach($items as $amenity)
                                                                <div class="amenities-item">
                                                                    <img src="{{ asset('img/icons/check.svg') }}"
                                                                         alt="{{ $amenity }}">
                                                                    <div class="name">{{ $amenity }}</div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="address">{{ $hotel?->__('address') }}</div>
                                                        <div class="btn-wrap">
                                                            <form action="{{ route('findHotelExely', $room->roomType->id) }}">
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
                                                                {{-- <input type="hidden" name="servicesQuantity" value="{{  }}">--}}
                                                                <input type="hidden" name="hotel"
                                                                       value="{{ $room->fullPlacementsName }}">
                                                                <input type="hidden" name="hotel_id"
                                                                       value="{{ $room->propertyId }}">
                                                                <input type="hidden" name="room_id"
                                                                       value="{{ $room->roomType->id }}">
                                                                <input type="hidden" name="title"
                                                                       value="{{ $room->fullPlacementsName }}">
                                                                <input type="hidden" name="price"
                                                                       value="{{ round($room->total->priceBeforeTax / 0.92) }}">
                                                                <button class="more">@lang('main.show_all_rooms')</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- TOURMIND --}}
                                    @elseif($item['source'] === 'tm')
                                        @php
                                            $tm    = $item['tm'] ?? [];
                                            $hotel = $item['hotel'] ?? null;

                                            $title   = $hotel?->title ?? ($tm->title ?? ($tm->localData->title ?? ''));
                                            $images  = $hotel ? Image::where('hotel_id', $hotel->id)->orderBy('id')->limit(2)->get() : collect();
                                            $tmImages = collect($tm->images ?? ($tm->localData->images ?? []));
                                            $amenStr = $hotel && $hotel->amenity ? $hotel->amenity->services : ($tm->localData->amenity->services ?? '');
                                            $amenities = $amenStr ? explode(',', $amenStr) : [];
                                            $items = array_slice($amenities, 0, 8);

                                            $iconMap = [
                                                'wi-fi'=>'wifi.svg','интернет'=>'wifi.svg','Доступ в интернет'=>'wifi.svg',
                                                'чайный набор'=>'tea.svg','Питание включено'=>'meal.svg','минеральная вода'=>'water.svg',
                                                'сауна'=>'sauna.svg','сейф'=>'safe.svg','Двуспальная кровать'=>'bed2.svg',
                                                'Гладильные принадлежности'=>'iron.svg','Ванная комната'=>'bath.svg','Минибар'=>'minibar.svg',
                                                'Кондиционер'=>'cond.svg','Туалетные принадлежности'=>'toilet.svg','Душ'=>'shower.svg',
                                                'Звукоизоляция'=>'sound.svg','Фен'=>'dry.svg','Постельное бельё'=>'bed_sheets.svg',
                                                'Халат'=>'robe.svg','Шкаф'=>'closet.svg','шкаф для одежды'=>'closet.svg',
                                                'Телефон'=>'phone_hotel.svg','Отопление'=>'heating.svg','Письменный стол'=>'table.svg',
                                                'Минеральная вода'=>'water.svg'
                                            ];

                                            $converted = $item['conv_total'];
                                            $symbol    = $item['conv_symbol'];
                                        @endphp
                                        <div class="search-item tm"
                                             data-id="{{ $hotel?->id ?? ($tm->hid ?? '') }}"
                                             data-type="{{ $hotel?->type ?? '' }}"
                                             data-title="{{ strtolower($title) }}"
                                             data-price="{{ $item['price'] }}">
                                            <div class="row">
                                                <div class="col-md-5 order-xl-1 order-lg-1 order-1">
                                                    <div class="img-wrap">
                                                        @if($images->count() >= 1)
                                                            <div class="row">
                                                                <div class="col-md-6 col-6">
                                                                    <div class="main">
                                                                        <img src="{{ Storage::url($images[0]->image) }}"
                                                                             alt="">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6 col-6">
                                                                    @if($images->count() >= 2)
                                                                        <div class="primary">
                                                                            <img src="{{ Storage::url($images[1]->image) }}"
                                                                                 alt="">
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @elseif($tmImages->count() >= 1)
                                                            <img src="{{ $tmImages->first() }}" alt="">
                                                        @else
                                                            @if($hotel?->image)
                                                                <img src="{{ Storage::url($hotel->image) }}" alt="">
                                                            @else
                                                                <img src="{{ route('index') }}/img/noimage.png" alt="">
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-md-7 order-xl-2 order-lg-2 order-2">
                                                    <div class="row">
                                                        <div class="col-md-7 col-8">
                                                            <h4>{{ $title }}</h4>
                                                            @if($hotel?->rating)
                                                                <div class="rating">
                                                                    <img src="{{ route('index') }}/img/star.svg"
                                                                         alt=""> {{ $hotel->rating }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-5 col-4">
                                                            <div class="price">
                                                                @lang('main.from') {{ number_format((int)$converted, 0, '.', ' ') }} {{ $symbol }}
                                                            </div>
                                                            <div class="night">@lang('main.night')</div>
                                                        </div>
                                                    </div>
                                                    <div class="amenities">
                                                        @foreach($items as $amenity)
                                                            @php
                                                                $iconFile = 'check.svg';
                                                                foreach ($iconMap as $keyword => $filename) {
                                                                    if (mb_stripos($amenity, $keyword) !== false) { $iconFile = $filename; break; }
                                                                }
                                                            @endphp
                                                            <div class="amenities-item">
                                                                <img src="{{ asset('img/icons/' . $iconFile) }}"
                                                                     alt="{{ $amenity }}">
                                                                <div class="name">{{ $amenity }}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="address">{{ $hotel?->__("address") }}</div>
                                                    <div class="btn-wrap">
                                                        <form action="{{ route('findHotel', $hotel?->code) }}">
                                                            <input type="hidden" name="arrivalDate"
                                                                   value="{{ $request->arrivalDate }}">
                                                            <input type="hidden" name="departureDate"
                                                                   value="{{ $request->departureDate }}">
                                                            {{-- Добавьте нужные поля TM при переходе --}}
                                                            <button class="more">@lang('main.show_all_rooms')</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- Карта --}}
                    <div class="col-lg-5 col-md-12">
                        <div class="map-sticky">
                            <div id="map"></div>
                        </div>

                        @php
                            $hotelse = collect($allHotels)
                                ->map(function ($item) {
                                    $h   = $item['hotel'] ?? null;
                                    $raw = $item[$item['source']] ?? null; // 'tm' | 'roomStay' и т.д.

                                    // id: локальный id, иначе TM hid
                                    $id = $h->id ?? ($raw->hid ?? null);

                                    return [
                                        'id'    => $id,
                                        'name'  => $h->title_en ?? $h->title ?? '',
                                        'lat'   => isset($h?->lat) ? (float) $h->lat : null,
                                        'lng'   => isset($h?->lng) ? (float) $h->lng : null,
                                        'img'   => $h && $h->image ? Storage::url($h->image) : '',
                                        'total' => $item['conv_total'] ?? '',
                                        'curr'  => $item['conv_symbol'] ?? '',
                                    ];
                                })
                                ->filter(fn ($i) => !empty($i['lat']) && !empty($i['lng']))
                                ->values();
                        @endphp

                        {{-- Leaflet --}}
                        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
                        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
                        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css"/>
                        <link rel="stylesheet"
                              href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css"/>
                        <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>

                        <script>
                            const hotels = @json($hotelse);

                            const map = L.map('map').setView([hotels[0]?.lat || 0, hotels[0]?.lng || 0], 9);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 14}).addTo(map);

                            const listItems = {};
                            const markers = L.markerClusterGroup();

                            hotels.forEach(hotel => {
                                const popupContent = `
                          <div class="d-flex align-items-center gap-2">
                            <img src="${hotel.img || '/img/noimage.png'}" class="rounded" style="width: 80px; height: 60px; object-fit: cover;">
                            <div>
                              <div class="fw-bold mb-1">${hotel.name}</div>
                              <div class="text-success fw-semibold"><strong>${hotel.total} ${hotel.curr}</strong></div>
                            </div>
                          </div>`;

                                const marker = L.marker([hotel.lat, hotel.lng]).bindPopup(popupContent);
                                marker.on('mouseover', () => marker.openPopup());
                                marker.on('mouseout', () => marker.closePopup());

                                // Связь с листингом
                                const li = document.querySelector(`div.search-item[data-id="${hotel.id}"]`);
                                listItems[hotel.id] = li;

                                marker.on('click', () => {
                                    Object.values(listItems).forEach(el => {
                                        if (el) el.classList.remove('active');
                                    });
                                    if (li) {
                                        li.classList.add('active');
                                        li.scrollIntoView({behavior: 'smooth', block: 'center'});
                                    }
                                });

                                markers.addLayer(marker);
                            });

                            map.addLayer(markers);
                        </script>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const input = document.getElementById('search-input');
                const sortSelect = document.getElementById('sort-client');
                const hotelList = document.getElementById('hotel-list');
                const clearBtn = document.getElementById('clear-filters');
                const checkboxes = document.querySelectorAll('.type-filter');
                const loadMoreBtn = document.getElementById('load-more');
                const filtersList = document.getElementById('filters-list');

                let items = [];
                let visibleCount = 5;

                input.focus();
                input.value = localStorage.getItem('searchQuery') || '';
                sortSelect.value = localStorage.getItem('sortOption') || '';

                function filterAndSort() {
                    const query = input.value.toLowerCase();
                    const sortType = sortSelect.value;

                    localStorage.setItem('searchQuery', input.value);
                    localStorage.setItem('sortOption', sortType);

                    const activeTypes = Array.from(checkboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);

                    // Получаем все .search-item на странице
                    const allItems = Array.from(document.querySelectorAll('.search-item'));

                    let filtered = allItems.filter(hotel => {
                        const title = hotel.dataset.title || '';
                        const type = hotel.dataset.type || '';
                        const matchesQuery = title.includes(query);
                        const matchesType = activeTypes.length === 0 || activeTypes.includes(type);
                        return matchesQuery && matchesType;
                    });

                    if (sortType === 'price-asc') {
                        filtered.sort((a, b) => +a.dataset.price - +b.dataset.price);
                    } else if (sortType === 'price-desc') {
                        filtered.sort((a, b) => +b.dataset.price - +a.dataset.price);
                    } else if (sortType === 'title-asc') {
                        filtered.sort((a, b) => (a.dataset.title || '').localeCompare(b.dataset.title || ''));
                    } else if (sortType === 'title-desc') {
                        filtered.sort((a, b) => (b.dataset.title || '').localeCompare(a.dataset.title || ''));
                    }

                    items = filtered;
                    visibleCount = 5;
                    renderVisibleItems();
                    updateBadges(query, activeTypes);
                }

                function renderVisibleItems() {
                    hotelList.innerHTML = '';

                    items.forEach((el, i) => {
                        el.style.display = '';
                        if (i < visibleCount) {
                            hotelList.appendChild(el);
                        }
                    });

                    loadMoreBtn.style.display = (visibleCount < items.length) ? 'inline-block' : 'none';
                }

                function updateBadges(query, activeTypes) {
                    if (!filtersList) return;
                    filtersList.innerHTML = '';

                    if (query) {
                        const badge = document.createElement('div');
                        badge.className = 'filter-badge';
                        badge.innerHTML = `Поиск: "${query}" <span class="remove-btn" data-type="query">×</span>`;
                        filtersList.appendChild(badge);
                    }

                    activeTypes.forEach(type => {
                        const badge = document.createElement('div');
                        badge.className = 'filter-badge';
                        badge.innerHTML = `${type} <span class="remove-btn" data-type="type" data-value="${type}">×</span>`;
                        filtersList.appendChild(badge);
                    });

                    filtersList.querySelectorAll('.remove-btn').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const type = btn.dataset.type;
                            const value = btn.dataset.value;

                            if (type === 'query') {
                                input.value = '';
                            } else if (type === 'type') {
                                const cb = Array.from(checkboxes).find(c => c.value === value);
                                if (cb) cb.checked = false;
                            }

                            filterAndSort();
                        });
                    });
                }

                // События
                input.addEventListener('input', filterAndSort);
                sortSelect.addEventListener('change', filterAndSort);
                checkboxes.forEach(cb => cb.addEventListener('change', filterAndSort));
                clearBtn?.addEventListener('click', () => {
                    input.value = '';
                    sortSelect.value = '';
                    checkboxes.forEach(cb => cb.checked = false);
                    localStorage.removeItem('searchQuery');
                    localStorage.removeItem('sortOption');
                    filterAndSort();
                    input.focus();
                });
                loadMoreBtn?.addEventListener('click', () => {
                    visibleCount += 5;
                    renderVisibleItems();
                });

                // Первичная фильтрация
                filterAndSort();
            });
        </script>

    @else
        @include('layouts.auth')
    @endauth

@endsection
