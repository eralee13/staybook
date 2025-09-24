@extends('layouts.main')

@section('title', 'StayBook – часть Silk Way Group')

@section('content')
    @auth
        <div class="main-filter">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>@lang('main.head')</h1>
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
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <input type="text" id="searchbox" name="city" placeholder="@lang('main.city_or_hotel')"
                                               autocomplete="off" value="{{ request('q') }}">
                                        <div id="suggest" class="suggest hidden"></div>
                                        <input type="hidden" name="city_id" id="city_id">
                                        <style>
                                            .suggest{position:absolute; z-index:9999; background:#fff; border:1px solid #e5e7eb; width:100%; max-height:280px; overflow:auto; border-radius:8px; box-shadow:0 10px 20px rgba(0,0,0,.08)}
                                            .suggest.hidden{display:none}
                                            .suggest-item{padding:10px 12px; cursor:pointer; display:flex; gap:8px; align-items:center}
                                            .suggest-item:hover, .suggest-item.active{background:#f3f4f6}
                                            .s-title{font-weight:600; font-size:14px}
                                            .s-sub{font-size:12px; color:#6b7280}
                                            .s-badge{font-size:11px; color:#111827; background:#fef3c7; border:1px solid #fcd34d; border-radius:6px; padding:2px 6px}
                                        </style>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', () => {
                                                const input   = document.getElementById('searchbox');
                                                const box     = document.getElementById('suggest');
                                                const url     = @json(route('suggest'));
                                                let items     = [];
                                                let activeIdx = -1;
                                                let lastQuery = '';
                                                let t = null;

                                                function debounce(fn, ms) {
                                                    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
                                                }

                                                function hide() { box.classList.add('hidden'); activeIdx = -1; }
                                                function show() { box.classList.remove('hidden'); }

                                                function render(list) {
                                                    if (!list.length) { hide(); return; }
                                                    box.innerHTML = list.map((it, i) => {
                                                        const rating = it.rating ? `<span class="s-badge">★ ${it.rating}</span>` : '';
                                                        const type   = it.type === 'hotel'
                                                            ? '<span class="s-badge">Отель</span>'
                                                            : '<span class="s-badge">Город</span>';
                                                        const alt  = it.alt && it.alt !== it.label
                                                            ? ` · <span class="s-sub">${it.alt}</span>` : '';
                                                        const city = it.city ? `<div class="s-sub">${it.city}</div>` : '';
                                                        return `
<div class="suggest-item" data-idx="${i}">
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
                                                            headers: { 'Accept': 'application/json' },
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
                                                    if (q.length < 2) { hide(); lastQuery = ''; return; }
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
                                                    const it  = items[idx];
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
                                                            el.scrollIntoView({ block: 'nearest' });
                                                        }
                                                    });
                                                }
                                            });
                                        </script>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <input type="text" id="arrivalDisplay" class="date" autocomplete="off">
                                        <input type="hidden" id="arrivalDate" name="arrivalDate"
                                               value="{{ now()->format('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <input type="text" id="departureDisplay" class="date" autocomplete="off">
                                        <input type="hidden" id="departureDate" name="departureDate"
                                               value="{{ $tomorrow }}">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
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
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="1">
                                                                <div class="img">
                                                                    <div class="num">1</div>
                                                                    <div class="img-wrap">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="2">
                                                                <div class="img">
                                                                    <div class="num">2</div>
                                                                    <div class="img-wrap">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="3">
                                                                <div class="img">
                                                                    <div class="num">3</div>
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
                                                        <div class="col-lg col-md-4 col-6">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="4">
                                                                <div class="img">
                                                                    <div class="num">4</div>
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
                                                        <div class="col-lg col-md-4 col-6">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="5">
                                                                <div class="img">
                                                                    <div class="num">5</div>
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
                                                    <div class="row justify-content-center">
                                                        @php
                                                            $meals = \App\Models\Meal::all();
                                                        @endphp
                                                        @foreach ($meals as $meal)
                                                            <div class="col-lg">
                                                                <div class="itemmm">
                                                                    <input type="checkbox" name="meal[]"
                                                                           id="{{ $meal->code }}"
                                                                           value="{{ $meal->id }}">
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
                                <div class="col-lg col-md-6">
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

        @if(app()->getLocale() == 'ru')
        <div class="vantages">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="text-wrap">
                            <h2>Преимущества StayBook</h2>
                        </div>
                        <h3>Для отелей <img src="img/arrow-right.svg" alt=""></h3>
                        <div class="owl-carousel owl-vantages">
                            <div class="vantages-item">
                                <img src="img/h1.svg" alt="">
                                <h5>Новые<br> клиенты</h5>
                                <p>Поток туристов, корпоративные заказы и группы</p>
                            </div>
                            <div class="vantages-item">
                                <img src="img/h2.svg" alt="">
                                <h5>Простое подключение</h5>
                                <p>Интеграция через Exely и Travelline без лишних кабинетов</p>
                            </div>
                            <div class="vantages-item">
                                <img src="img/h3.svg" alt="">
                                <h5>Гибкие<br> тарифы</h5>
                                <p>Отель сам управляет ценами и условиями </p>
                            </div>
                            <div class="vantages-item">
                                <img src="img/h4.svg" alt="">
                                <h5>Все услуги в одном месте</h5>
                                <p>Проживание, трансфер, дополнительные сервисы</p>
                            </div>

                            <div class="vantages-item">
                                <img src="img/h5.svg" alt="">
                                <h5>Финансовая прозрачность</h5>
                                <p>Cвоевременные выплаты без скрытых комиссий</p>
                            </div>
                            <div class="vantages-item">
                                <img src="img/h6.svg" alt="">
                                <h5>Поддержка<br> 24/7</h5>
                                <p>Оперативная помощь для отелей и гостей</p>
                            </div>
                            <div class="vantages-item">
                                <img src="img/h7.svg" alt="">
                                <h5>Продвижение и маркетинг</h5>
                                <p>Приоритетная выдача и реклама на платформе</p>
                            </div>
                            <div class="vantages-item">
                                <img src="img/h8.svg" alt="">
                                <h5>Успешные<br> кейсы</h5>
                                <p>Уже работают крупные отели, включая «Май хотел Бишкек»</p>
                            </div>
                        </div>


                        <h3 style="margin-top: 40px">Для партнеров <img src="img/arrow-right.svg" alt=""></h3>
                        <div class="owl-carousel owl-vantages">
                            <div class="vantages-item">
                                <img src="img/pa1.svg" alt="">
                                <h5>Прямой доступ к базе</h5>
                                <p>Актуальные цены и наличие в реальном времени</p>
                            </div>
                            <div class="vantages-item">
                                <img src="img/pa2.svg" alt="">
                                <h5>API <br>интеграция</h5>
                                <p>Быстрое подключение без сложной IT-поддержки</p>
                            </div>
                            <div class="vantages-item">
                                <img src="img/pa3.svg" alt="">
                                <h5>Управление онлайн</h5>
                                <p>Бронирования, отмены, изменения в единой системе</p>
                            </div>
                            <div class="vantages-item">
                                <img src="img/pa4.svg" alt="">
                                <h5>Широкий ассортимент</h5>
                                <p>Отели Кыргызстана и за его пределами </p>
                            </div>


                            <div class="vantages-item">
                                <img src="img/pa5.svg" alt="">
                                <h5>Прозрачные расчёты</h5>
                                <p>Удобная система выплат и документооборот</p>
                            </div>
                            <div class="vantages-item">
                                <img src="img/pa6.svg" alt="">
                                <h5>Техпоdддержка 24/7</h5>
                                <p>Сопровождение при интеграции и в работе</p>
                            </div>
                            <div class="vantages-item">
                                <img src="img/pa7.svg" alt="">
                                <h5>Рост<br> дохода</h5>
                                <p>Дополнительный канал продаж и новые клиенты</p>
                            </div>
                            <div class="vantages-item">
                                <img src="img/pa8.svg" alt="">
                                <h5>Гибкость<br><span style="color: transparent">Гибкость</span></h5>
                                <p>Кастомизация тарифов, валют и условий под бизнес-процессы</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="partners">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h3>Наши партнеры <img src="img/arrow-right.svg" alt=""></h3>
                        <div class="owl-carousel owl-partners">
                            <div class="partners-item">
                                <div class="img-wrap">
                                    <img src="img/part1.png" alt="">
                                </div>
                                <h6>Ассоциация туристических организаций</h6>
                            </div>
                            <div class="partners-item">
                                <div class="img-wrap">
                                    <img src="img/part2.png" alt="">
                                </div>
                                <h6>Департамент Туризма</h6>
                            </div>
                            <div class="partners-item">
                                <div class="img-wrap">
                                    <img src="img/part3.png" alt="">
                                </div>
                                <h6>Chanel manager Exely (Traveline)</h6>
                            </div>
                            <div class="partners-item">
                                <div class="img-wrap">
                                    <img src="img/silkwaytravelkg.png" alt="">
                                </div>
                                <h6>Турагентство Silk Way Travel</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="subscribe">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="wrap">
                            <h3>Подписывайтесь на наш телеграм канал</h3>
                            <p>Подписывайтесь на наш телеграм канал, чтобы всегда быть в курсе всех скидок и
                                спецпредложений, а также получать интересные статьи для вдохновения и различные лайфхаки
                                для путешествий.</p>
                            <div class="btn-wrap">
                                <a href="https://t.me/staybook" class="more" target="_blank">Подписаться</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @else
            <div class="vantages">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="text-wrap">
                                <h2>Advantages of StayBook</h2>
                            </div>
                            <h3>For hotels <img src="img/arrow-right.svg" alt=""></h3>
                            <div class="owl-carousel owl-vantages">
                                <div class="vantages-item">
                                    <img src="img/h1.svg" alt="">
                                    <h5>New<br> clients</h5>
                                    <p>Inbound tourists, corporate bookings, and group reservations</p>
                                </div>
                                <div class="vantages-item">
                                    <img src="img/h2.svg" alt="">
                                    <h5>Easy <br>integration</h5>
                                    <p>Integration via Exely and Travelline without extra accounts</p>
                                </div>
                                <div class="vantages-item">
                                    <img src="img/h3.svg" alt="">
                                    <h5>Flexible <br> rates</h5>
                                    <p>Full control over pricing and booking policies</p>
                                </div>
                                <div class="vantages-item">
                                    <img src="img/h4.svg" alt="">
                                    <h5>All-in-one <br> services</h5>
                                    <p>Accommodation, transfers, and extra services</p>
                                </div>

                                <div class="vantages-item">
                                    <img src="img/h5.svg" alt="">
                                    <h5>Financial transparency</h5>
                                    <p>Timely payments with no hidden fees</p>
                                </div>
                                <div class="vantages-item">
                                    <img src="img/h6.svg" alt="">
                                    <h5>Support<br> 24/7</h5>
                                    <p>Prompt support for hotels and guests</p>
                                </div>
                                <div class="vantages-item">
                                    <img src="img/h7.svg" alt="">
                                    <h5>Marketing & promotion</h5>
                                    <p>Priority listing and advertising on the platform</p>
                                </div>
                                <div class="vantages-item">
                                    <img src="img/h8.svg" alt="">
                                    <h5>Success <br> stories</h5>
                                    <p>Leading hotels are already onboard, including “My Hotel Bishkek”</p>
                                </div>
                            </div>


                            <h3 style="margin-top: 40px">For partners <img src="img/arrow-right.svg" alt=""></h3>
                            <div class="owl-carousel owl-vantages">
                                <div class="vantages-item">
                                    <img src="img/pa1.svg" alt="">
                                    <h5>Direct access to the database</h5>
                                    <p>Real-time prices and availability</p>
                                </div>
                                <div class="vantages-item">
                                    <img src="img/pa2.svg" alt="">
                                    <h5>API <br>integration</h5>
                                    <p>Fast setup without complex IT support</p>
                                </div>
                                <div class="vantages-item">
                                    <img src="img/pa3.svg" alt="">
                                    <h5>Online management</h5>
                                    <p>Reservations, cancellations, and changes in one system</p>
                                </div>
                                <div class="vantages-item">
                                    <img src="img/pa4.svg" alt="">
                                    <h5>Extensive selection</h5>
                                    <p>Hotels in Kyrgyzstan and beyond</p>
                                </div>


                                <div class="vantages-item">
                                    <img src="img/pa5.svg" alt="">
                                    <h5>Transparent payments</h5>
                                    <p>Convenient payment system and document management</p>
                                </div>
                                <div class="vantages-item">
                                    <img src="img/pa6.svg" alt="">
                                    <h5>Technical support 24/7</h5>
                                    <p>Support during integration and operation</p>
                                </div>
                                <div class="vantages-item">
                                    <img src="img/pa7.svg" alt="">
                                    <h5>Revenue <br>growth</h5>
                                    <p>Additional sales channel and new customers</p>
                                </div>
                                <div class="vantages-item">
                                    <img src="img/pa8.svg" alt="">
                                    <h5>Flexibility<br><span style="color: transparent">Flexibility</span></h5>
                                    <p>Customizable rates, currencies, and terms tailored to business processes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="partners">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h3>Our partners <img src="img/arrow-right.svg" alt=""></h3>
                            <div class="owl-carousel owl-partners">
                                <div class="partners-item">
                                    <div class="img-wrap">
                                        <img src="img/part1.png" alt="">
                                    </div>
                                    <h6>Association of Tourism Organizations</h6>
                                </div>
                                <div class="partners-item">
                                    <div class="img-wrap">
                                        <img src="img/part2.png" alt="">
                                    </div>
                                    <h6>Department of Tourism</h6>
                                </div>
                                <div class="partners-item">
                                    <div class="img-wrap">
                                        <img src="img/part3.png" alt="">
                                    </div>
                                    <h6>Chanel manager Exely (Traveline)</h6>
                                </div>
                                <div class="partners-item">
                                    <div class="img-wrap">
                                        <img src="img/silkwaytravelkg.png" alt="">
                                    </div>
                                    <h6>Silk Way Travel agency</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="subscribe">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="wrap">
                                <h3>Subscribe to our Telegram channel</h3>
                                <p>Follow our Telegram channel to never miss discounts, special offers, inspiring articles, and handy travel tips.</p>
                                <div class="btn-wrap">
                                    <a href="https://t.me/staybook" class="more" target="_blank">Subscribe</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @else
        <script>
            window.location.href = "{{ route('extranet') }}";
        </script>
    @endauth
@endsection
