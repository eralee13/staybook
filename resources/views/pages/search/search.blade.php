@php
    use App\Models\Image;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Auth;

    // Роль без учета регистра
    $isHotelios = Auth::check() && Auth::user()
        ? Auth::user()->roles->pluck('name')->map(fn($n) => mb_strtolower($n))->contains('hotelios')
        : false;

    // Коэффициенты из config/pricing.php
    $markupCoef = (float) ($isHotelios
        ? config('pricing.hotelios', 1.05)
        : config('pricing.default', 1.08)
    );

    $applyMarkup = function ($value) use ($markupCoef) {
        if ($value === null || $value === '') return null;
        $num = is_numeric($value) ? (float) $value : 0.0;
        return (int) ceil($num * $markupCoef);
    };
@endphp

@extends('layouts.main')

@section('title', 'Поиск')

@section('content')
    @auth
        <div class="main-filter">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <h1>@lang('main.head')</h1>
                        <div class="type">
                            <div class="type-item current">
                                <a href="{{ route('index') }}">@lang('main.hotels_and_rooms')</a>
                            </div>
                            <div class="type-item off">
                                <a href="{{ route('offline') }}">@lang('main.offline')</a>
                            </div>
                        </div>
                        <form action="{{ route('search') }}" method="GET">
                            <div class="row">
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <input type="text" id="searchbox" name="city"
                                               placeholder="@lang('main.city_or_hotel')"
                                               autocomplete="off" value="{{ $request->city }}" required>
                                        <img src="{{ route('index') }}/img/arrow_down.svg" alt="">
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
                                <div class="col-lg-4 col-md-6 extra">
                                    <div class="form-group">
                                        <img src="{{ route('index') }}/img/filter.svg" style="top: 12px">
                                        <div id="filter">
                                            <div class="label filter">@lang('main.filters')</div>
                                            <div class="filter-wrap" id="filter-wrap">
                                                <div class="closebtn" id="closebtn"><img
                                                            src="{{route('index')}}/img/close_btn.svg" alt=""></div>
                                                <h5>@lang('main.filters')</h5>
                                                <div class="form-group">
                                                    <div class="name">@lang('main.rating')</div>
                                                    <div class="row justify-content-center">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <div class="col">
                                                                <div class="item">
                                                                    <input class="rating-input"
                                                                           type="radio"
                                                                           name="rating"
                                                                           id="rating-{{ $i }}"
                                                                           value="{{ $i }}"
                                                                            {{ (int)request('rating') === $i ? 'checked' : '' }}>
                                                                    <label class="img {{ (int)request('rating') === $i ? 'active' : '' }}"
                                                                           for="rating-{{ $i }}">
                                                                        <div class="num">{{ $i }}</div>
                                                                        <div class="img-wrap">
                                                                            <svg width="36" height="36"
                                                                                 viewBox="0 0 36 36" fill="none"
                                                                                 xmlns="http://www.w3.org/2000/svg">
                                                                                <path fill-rule="evenodd"
                                                                                      clip-rule="evenodd"
                                                                                      d="M16.3808 4.30595C16.5459 4.02269 16.7824 3.78766 17.0666 3.62431C17.3509 3.46096 17.673 3.375 18.0008 3.375C18.3287 3.375 18.6508 3.46096 18.9351 3.62431C19.2193 3.78766 19.4558 4.02269 19.6208 4.30595L23.8133 11.503L31.9553 13.267C32.2756 13.3365 32.572 13.4889 32.8151 13.7088C33.0581 13.9286 33.2393 14.2084 33.3405 14.5201C33.4417 14.8318 33.4595 15.1646 33.392 15.4853C33.3245 15.8061 33.1741 16.1035 32.9558 16.348L27.4058 22.5595L28.2458 30.847C28.279 31.1733 28.2259 31.5026 28.092 31.802C27.958 32.1014 27.7479 32.3605 27.4825 32.5533C27.2172 32.7461 26.9059 32.8659 26.5797 32.9008C26.2535 32.9356 25.924 32.8843 25.6238 32.752L18.0008 29.392L10.3778 32.752C10.0777 32.8843 9.74812 32.9356 9.42196 32.9008C9.09581 32.8659 8.78451 32.7461 8.51914 32.5533C8.25377 32.3605 8.04363 32.1014 7.90969 31.802C7.77574 31.5026 7.72269 31.1733 7.75583 30.847L8.59583 22.5595L3.04583 16.3495C2.82717 16.105 2.67647 15.8074 2.60875 15.4865C2.54104 15.1656 2.55869 14.8325 2.65995 14.5206C2.7612 14.2086 2.94252 13.9287 3.18579 13.7087C3.42906 13.4887 3.72579 13.3364 4.04633 13.267L12.1883 11.503L16.3808 4.30595ZM18.0008 7.48445L14.5313 13.4425C14.4001 13.6673 14.2236 13.8624 14.0128 14.0153C13.8021 14.1682 13.5618 14.2755 13.3073 14.3305L6.56933 15.79L11.1623 20.9305C11.5133 21.3235 11.6828 21.8455 11.6303 22.369L10.9358 29.2285L17.2448 26.4475C17.4831 26.3425 17.7405 26.2883 18.0008 26.2883C18.2611 26.2883 18.5186 26.3425 18.7568 26.4475L25.0658 29.2285L24.3713 22.369C24.345 22.1099 24.3728 21.8483 24.4531 21.6006C24.5334 21.3529 24.6645 21.1247 24.8378 20.9305L29.4323 15.79L22.6943 14.3305C22.4398 14.2755 22.1996 14.1682 21.9888 14.0153C21.7781 13.8624 21.6015 13.6673 21.4703 13.4425L18.0008 7.48445Z"
                                                                                      fill="black"/>
                                                                            </svg>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endfor
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
                                                                <div class="col">
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
                                            <h5>@lang('main.guests_and_rooms')</h5>
                                            <div class="close-btn">
                                                <a href="javascript:void(0)"
                                                   id="panel-close"><img src="{{ route('index') }}/img/close_btn.svg"
                                                                         alt=""></a>
                                            </div>
                                            {{-- Кнопка добавить комнату --}}
                                            <div class="add-btn">
                                                <a href="javascript:void(0)" class="more"
                                                   id="add-room">
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
                                                    <div class="col-md-6 col-6">
                                                        <h4 class="flex justify-between items-center text-sm font-medium mb-3">
                                                            <span class="room-number">__NUM__</span> @lang('main.room')
                                                        </h4>
                                                    </div>
                                                    <div class="col-md-6 col-6">
                                                        <div class="remove-btn">
                                                            <a href="javascript:void(0)"
                                                               class="remove-room">
                                                                <img src="{{ route('index') }}/img/minus.svg" alt="">
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

                                @php
                                    $getSource = fn($item) => is_array($item) ? ($item['source'] ?? null) : ($item->source ?? null);
                                    $getHotel  = fn($item) => is_array($item) ? ($item['hotel'] ?? null)  : $item;

                                    $getConvTotal  = fn($item) => is_array($item) ? ($item['conv_total'] ?? null) : ($item->conv_total ?? null);
                                    $getConvSymbol = fn($item) => is_array($item) ? ($item['conv_symbol'] ?? '') : ($item->conv_symbol ?? '');
                                    $getRoomStay   = fn($item) => is_array($item) ? ($item['roomStay'] ?? null) : null;

                                    // 1) Собираем ID отелей (local + exely hotel)
                                    $hotelIds = collect($allHotels)
                                        ->map(fn($it) => $getHotel($it)?->id)
                                        ->filter()
                                        ->unique()
                                        ->values()
                                        ->all();

                                    // 2) Картинки одним запросом
                                    $imagesByHotel = \App\Models\Image::query()
                                        ->whereIn('hotel_id', $hotelIds)
                                        ->orderBy('id')
                                        ->get()
                                        ->groupBy('hotel_id');

                                    // 3) Amenities одним запросом
                                    $amenByHotel = \App\Models\Amenity::query()
                                        ->whereIn('hotel_id', $hotelIds)
                                        ->get()
                                        ->keyBy('hotel_id');

                                    $getAmenities = function ($hotelId) use ($amenByHotel) {
                                        $obj = $amenByHotel->get($hotelId);
                                        $str = $obj?->services ?? '';
                                        $arr = $str ? explode(',', $str) : [];
                                        return array_slice($arr, 0, 8);
                                    };

                                    $getSlides = function ($hotel) use ($imagesByHotel) {
                                        if (!$hotel?->id) return collect();
                                        return ($imagesByHotel->get($hotel->id) ?? collect())->take(3);
                                    };
                                @endphp

                                @foreach($allHotels as $item)
                                    @php
                                        $source = $getSource($item);
                                        $hotel  = $getHotel($item);

                                        if (!$hotel || !$hotel?->id) continue;

                                        $title        = $hotel->title ?? $hotel->title_en ?? '';
                                        $convertedRaw = $getConvTotal($item);
                                        $converted    = $applyMarkup($convertedRaw);
                                        $symbol       = $getConvSymbol($item);

                                        // ✅ ВАЖНО: эти переменные нужны в шаблоне
                                        $slides    = $getSlides($hotel);
                                        $itemsAmen = $getAmenities($hotel->id);
                                    @endphp

                                    {{-- LOCAL --}}
                                    @if($source === 'local')
                                        <div class="search-item"
                                             data-id="{{ $hotel->id }}"
                                             data-type="{{ $hotel->type ?? '' }}"
                                             data-title="{{ strtolower($title) }}"
                                             data-price="{{ $converted }}">
                                            <div class="row">
                                                <div class="col-md-6 order-xl-1 order-lg-1 order-1">
                                                    <div class="img-wrap">
                                                        <div class="owl-carousel owl-slider">
                                                            <div class="slider-item">
                                                                @if($hotel->image)
                                                                    <img src="{{ Storage::url($hotel->image) }}" alt="">
                                                                @elseif($slides->isNotEmpty())
                                                                    @foreach($slides as $file)
                                                                        <div class="primary">
                                                                            <img src="{{ Storage::url($file->image) }}" alt="">
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <img src="{{ route('index') }}/img/noimage.png" alt="">
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
                                                                    {{ $hotel->rating }}
                                                                    <img src="{{ route('index') }}/img/star.svg" alt="">
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 order-xl-2 order-lg-2 order-2">
                                                    <div class="wrap">
                                                        <h4>{{ $hotel->__('title') }}</h4>

                                                        <div class="amenities">
                                                            @foreach($itemsAmen as $amenity)
                                                                <div class="amenities-item">
                                                                    <img src="{{ asset('img/icons/check.svg') }}" alt="{{ $amenity }}">
                                                                    <div class="name">{{ $amenity }}</div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <div class="address">{{ $hotel->__('address') }}</div>

                                                        <div class="btn-wrap">
                                                            <form action="{{ route('findHotel', $hotel->code) }}">
                                                                <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                                                                <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                                                                <input type="hidden" name="roomCount" value="{{ $roomCount }}">
                                                                <input type="hidden" name="adult" value="{{ $totalAdults }}">
                                                                <input type="hidden" name="child" value="{{ $totalChildren }}">

                                                                @foreach(($childAges ?? []) as $age)
                                                                    <input type="hidden" name="childAges[]" value="{{ $age }}">
                                                                @endforeach

                                                                @foreach((array) $request->meal as $meal)
                                                                    <input type="hidden" name="meal[]" value="{{ $meal }}">
                                                                @endforeach

                                                                <button class="more">@lang('main.show_all_rooms')</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- EXELY --}}
                                    @elseif($source === 'exely')
                                        @php
                                            $room = $getRoomStay($item);

                                            // если roomStay отсутствует — не падаем, просто не рисуем кнопку
                                            $propertyId = data_get($room, 'propertyId');
                                            $adultCount = data_get($room, 'guestCount.adultCount', 1);
                                            $childAges  = (array) data_get($room, 'guestCount.childAges', []);
                                            $ratePlanId = data_get($room, 'ratePlan.id');
                                            $checksum   = data_get($room, 'checksum');
                                            $roomTypeId = data_get($room, 'roomType.id');
                                            $roomTitle  = data_get($room, 'fullPlacementsName');
                                            $priceRaw   = data_get($room, 'total.priceBeforeTax', 0);
                                        @endphp

                                        <div class="search-item"
                                             data-id="{{ $hotel->id }}"
                                             data-type="{{ $hotel->type ?? '' }}"
                                             data-title="{{ strtolower($title) }}"
                                             data-price="{{ $converted }}">
                                            <div class="row">
                                                <div class="col-md-6 order-xl-1 order-lg-1 order-1">
                                                    <div class="img-wrap">
                                                        <div class="owl-carousel owl-slider">
                                                            <div class="slider-item">
                                                                @if($hotel->image)
                                                                    <img src="{{ Storage::url($hotel->image) }}" alt="">
                                                                @elseif($slides->isNotEmpty())
                                                                    @foreach($slides as $file)
                                                                        <div class="primary">
                                                                            <img src="{{ Storage::url($file->image) }}" alt="">
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <img src="{{ route('index')}}/img/noimage.png" alt="">
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
                                                                    <img src="{{ route('index') }}/img/star.svg" alt="">
                                                                    {{ $hotel->rating }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 order-xl-2 order-lg-2 order-2">
                                                    <div class="wrap">
                                                        <h4>{{ $hotel->title }}</h4>

                                                        <div class="amenities">
                                                            @foreach($itemsAmen as $amenity)
                                                                <div class="amenities-item">
                                                                    <img src="{{ asset('img/icons/check.svg') }}" alt="{{ $amenity }}">
                                                                    <div class="name">{{ $amenity }}</div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <div class="address">{{ $hotel->__('address') }}</div>

                                                        <div class="btn-wrap">
                                                            {{-- ✅ не падаем если propertyId отсутствует --}}
                                                            @if(!empty($propertyId))
                                                                <form action="{{ route('findHotelExely', $propertyId) }}">
                                                                    <input type="hidden" name="propertyId" value="{{ $propertyId }}">
                                                                    <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                                                                    <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                                                                    <input type="hidden" name="adultCount" value="{{ $adultCount }}">

                                                                    @if(!empty($childAges))
                                                                        <input type="hidden" name="childAges[]" value="{{ implode(',', $childAges) }}">
                                                                    @endif

                                                                    <input type="hidden" name="ratePlanId" value="{{ $ratePlanId }}">
                                                                    <input type="hidden" name="checkSum" value="{{ $checksum }}">
                                                                    <input type="hidden" name="roomTypeId" value="{{ $roomTypeId }}">

                                                                    <input type="hidden" name="title" value="{{ $roomTitle }}">
                                                                    <input type="hidden" name="price" value="{{ $applyMarkup($priceRaw) ?? $priceRaw }}">

                                                                    <button class="more">@lang('main.show_all_rooms')</button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- TOURMIND --}}
                                    @elseif($source === 'tm')
                                        {{-- вставь сюда твой существующий TM блок без изменений --}}
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
                                ->map(function ($item) use ($getHotel, $getConvTotal, $getConvSymbol, $applyMarkup) {
                                    $hotel = $getHotel($item);
                                    if (!$hotel?->id) return null;

                                    $totalRaw = $getConvTotal($item);
                                    $total = $applyMarkup($totalRaw) ?? '';

                                    return [
                                        'id'    => $hotel->id,
                                        'name'  => $hotel->title_en ?? $hotel->title ?? '',
                                        'lat'   => $hotel->lat ? (float) $hotel->lat : null,
                                        'lng'   => $hotel->lng ? (float) $hotel->lng : null,
                                        'img'   => $hotel->image ? Storage::url($hotel->image) : '',
                                        'total' => $total,
                                        'curr'  => $getConvSymbol($item),
                                    ];
                                })
                                ->filter()
                                ->filter(fn ($i) => !empty($i['lat']) && !empty($i['lng']))
                                ->values();
                        @endphp

                        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
                        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
                        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css"/>
                        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css"/>
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

                                const li = document.querySelector(`div.search-item[data-id="${hotel.id}"]`);
                                listItems[hotel.id] = li;

                                marker.on('click', () => {
                                    Object.values(listItems).forEach(el => el?.classList.remove('active'));
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
                const input = document.getElementById('searchbox');
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
