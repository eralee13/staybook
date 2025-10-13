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
                            <div class="type-item off">
                                <a href="{{ route('offline') }}">@lang('main.offline')</a>
                            </div>
                        </div>
                        <form action="{{ route('search') }}" method="GET">
                            <div class="row">

                                {{-- ПОИСК --}}
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group position-relative">
                                        <input
                                                id="searchInput"
                                                name="city"
                                                class="form-control"
                                                value="{{ old('city', $q) }}"
                                                placeholder="Введите город/страну: Бишкек / Bishkek / KGS / Кыргызстан…"
                                                autocomplete="off"
                                                required
                                        />
                                        <input type="hidden" name="city_id" id="city_id">
                                        <script>const SUGGEST_URL = @json(route('search.suggest'));</script>
                                        <img src="{{ route('index') }}/img/arrow_down.svg" alt="">
                                        @if(isset($city) || isset($country))
                                            <p class="text-muted mt-2 mb-0">
                                                @if($city) Город: <strong>{{ $city->title }}</strong>@endif
                                                @if($country) &nbsp; Страна: <strong>{{ $country->name }}</strong>@endif
                                            </p>
                                        @endif>

                                        {{-- Выпадающие подсказки --}}
                                        <ul id="suggestBox"
                                            class="list-group position-absolute"
                                            style="z-index:1000; max-height:320px; overflow:auto; display:none; width:100%">
                                        </ul>

                                        <script>
                                            (function () {
                                                const input = document.getElementById('searchInput');
                                                const box   = document.getElementById('suggestBox');
                                                const cityId= document.getElementById('city_id');
                                                let t = null, items = [];

                                                function show(list) {
                                                    if (!list.length) { box.style.display='none'; box.innerHTML=''; return; }
                                                    box.innerHTML = list.map((it, idx) => {
                                                        const isHotel = it.type === 'hotel';
                                                        const icon = isHotel ? '🏨' : '🏙️';
                                                        const sub  = isHotel
                                                            ? (it.city ?? '')
                                                            : (it.country_code ?? it.alpha2 ?? '');
                                                        const rating = (isHotel && it.rating) ? ` <span class="badge bg-warning text-dark">★ ${it.rating}</span>` : '';
                                                        return `<li class="list-group-item list-group-item-action"
                                                                    data-idx="${idx}">
                                                                    ${icon} <strong>${it.label ?? it.name}</strong>${rating}
                                                                    <div class="text-muted small">${sub ?? ''}</div>
                                                                </li>`;
                                                    }).join('');
                                                    box.style.display = 'block';
                                                }

                                                function hide() {
                                                    box.style.display='none'; box.innerHTML='';
                                                }

                                                async function fetchSuggest(q) {
                                                    try {
                                                        const res = await fetch(SUGGEST_URL + '?q=' + encodeURIComponent(q), {
                                                            headers: { 'Accept': 'application/json' },
                                                            cache: 'no-store'
                                                        });
                                                        if (!res.ok) throw new Error('HTTP ' + res.status);
                                                        const data = await res.json();
                                                        const list = Array.isArray(data) ? data : (Array.isArray(data.items) ? data.items : []);
                                                        // нормализуем поля под фронт
                                                        items = list.map(it => ({
                                                            type: it.type ?? (it.city_id ? 'city' : 'hotel'),
                                                            label: it.label ?? it.name ?? '',
                                                            city: it.city ?? it.alt ?? '',
                                                            city_id: it.city_id ?? it.id ?? null,
                                                            rating: it.rating ?? null,
                                                            country_code: it.country_code ?? it.alpha2 ?? null
                                                        }));
                                                        show(items);
                                                    } catch (e) {
                                                        console.error('[suggest]', e);
                                                        hide();
                                                    }
                                                }

                                                input.addEventListener('input', () => {
                                                    clearTimeout(t);
                                                    const v = input.value.trim();
                                                    if (v.length < 2) { hide(); return; }
                                                    t = setTimeout(() => fetchSuggest(v), 200);
                                                });

                                                box.addEventListener('click', e => {
                                                    const li = e.target.closest('li'); if (!li) return;
                                                    const idx = +li.dataset.idx; const it = items[idx]; if (!it) return;
                                                    input.value = it.label;
                                                    if (it.city_id) cityId.value = it.city_id;
                                                    hide();
                                                    // по желанию: input.form.submit();
                                                });

                                                document.addEventListener('click', e => {
                                                    if (!box.contains(e.target) && e.target !== input) hide();
                                                });

                                                input.addEventListener('focus', () => { if (items.length) show(items); });
                                            })();
                                        </script>
                                    </div>
                                </div>

                                {{-- ДАТЫ --}}
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <input type="text" id="arrivalDisplay" class="date" autocomplete="off">
                                        <input type="hidden" id="arrivalDate" name="arrivalDate" value="{{ now()->format('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <input type="text" id="departureDisplay" class="date" autocomplete="off">
                                        <input type="hidden" id="departureDate" name="departureDate" value="{{ $tomorrow }}">
                                    </div>
                                </div>

                                {{-- ФИЛЬТРЫ --}}
                                <div class="col-lg-4 col-md-6 extra">
                                    <div class="form-group">
                                        <img src="{{ route('index') }}/img/filter.svg" style="top: 12px">
                                        <div id="filter">
                                            <div class="label filter">@lang('main.filters')</div>
                                            <div class="filter-wrap" id="filter-wrap">
                                                <div class="closebtn" id="closebtn">
                                                    <img src="{{route('index')}}/img/close_btn.svg" alt="">
                                                </div>
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
                                                                    <label class="img {{ (int)request('rating') === $i ? 'active' : '' }}" for="rating-{{ $i }}">
                                                                        <div class="num">{{ $i }}</div>
                                                                        <div class="img-wrap">
                                                                            {{-- иконка звезды --}}
                                                                            <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                <path fill-rule="evenodd" clip-rule="evenodd"
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

                                                <div class="line"></div>
                                                <div class="name">@lang('main.arrival')</div>
                                                <div class="form-group" id="income">
                                                    <div class="row">
                                                        <div class="col-md-6 col-6">
                                                            <div class="itemm">
                                                                <input type="checkbox" value="early_in">
                                                                <label>@lang('main.early_in')</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-6">
                                                            <div class="itemm">
                                                                <input type="checkbox" value="late_out">
                                                                <label>@lang('main.late_out')</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="line"></div>
                                                <div class="form-group" id="meal">
                                                    <div class="name">@lang('main.meal_plans')</div>
                                                    <div class="row justify-content-center">
                                                        @php($meals = \App\Models\Meal::all())
                                                        @foreach ($meals as $meal)
                                                            <div class="col">
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

                                                <div class="more" id="btnclose">@lang('main.apply')</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ГОСТИ/КОМНАТЫ (оставлено как есть) --}}
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <a href="javascript:void(0)" id="rooms-summary">
                                            @lang('main.room'): 1, @lang('main.adult'): 1, @lang('main.child'): 0
                                        </a>

                                        <div id="rooms-panel-overlay" class="hidden"></div>
                                        <div id="rooms-panel">
                                            <h5>@lang('main.guests_and_rooms')</h5>
                                            <div class="close-btn">
                                                <a href="javascript:void(0)" id="panel-close">
                                                    <img src="{{ route('index') }}/img/close_btn.svg" alt="">
                                                </a>
                                            </div>

                                            <div class="add-btn">
                                                <a href="javascript:void(0)" id="add-room" class="more">
                                                    @lang('main.add_room')
                                                </a>
                                            </div>

                                            <div id="rooms-container"></div>

                                            <div class="mt-4">
                                                <button id="panel-apply" class="more">
                                                    @lang('main.ready')
                                                </button>
                                            </div>
                                        </div>

                                        <template id="room-template">
                                            <div class="guest-room" data-index="__INDEX__">
                                                <div class="row">
                                                    <div class="col-md-6 col-6">
                                                        <h4><span class="room-number">__NUM__</span> @lang('main.room')</h4>
                                                    </div>
                                                    <div class="col-md-6 col-6">
                                                        <div class="remove-btn">
                                                            <a href="javascript:void(0)" class="remove-room">
                                                                <img src="{{ route('index') }}/img/minus.svg" alt="">
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="rooms[__INDEX__][adults]" value="1" class="input-adults">

                                                <a href="javascript:void(0)" class="guest-summary">
                                                    <span class="summary-text">1 @lang('main.adult')</span>
                                                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M9.73604 9.99399L20.261 9.99399C20.6766 9.99339 21.0837 10.1105 21.4355 10.3317C21.7872 10.5529 22.0691 10.8692 22.2485 11.244C22.4586 11.6887 22.5395 12.1834 22.482 12.6718C22.4246 13.1602 22.231 13.6227 21.9235 14.0065L16.661 20.3815C16.4545 20.6198 16.1992 20.8109 15.9123 20.9419C15.6255 21.0728 15.3139 21.1406 14.9985 21.1406C14.6832 21.1406 14.3716 21.0728 14.0847 20.9419C13.7979 20.8109 13.5426 20.6198 13.336 20.3815L8.07354 14.0065C7.76603 13.6227 7.57252 13.1602 7.51506 12.6718C7.4576 12.1834 7.5385 11.6887 7.74854 11.244C7.92797 10.8692 8.20986 10.5529 8.5616 10.3317C8.91333 10.1105 9.32053 9.99339 9.73604 9.99399Z" fill="black"/>
                                                    </svg>
                                                </a>

                                                <div class="guest-dropdown hidden">
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
                                                        <button class="apply-guests">@lang('main.apply')</button>
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

                                                function openPanel() { overlay.classList.remove('hidden'); panel.classList.add('open'); }
                                                function closePanel(){ panel.classList.remove('open'); overlay.classList.add('hidden'); }

                                                function updateGlobalSummary() {
                                                    const rooms = roomsContainer.querySelectorAll('.guest-room');
                                                    const roomCount = rooms.length;
                                                    let adultsTotal = 0, childrenTotal = 0;
                                                    rooms.forEach(r => {
                                                        adultsTotal += +r.querySelector('.count-adult').textContent;
                                                        childrenTotal += +r.querySelector('.count-child').textContent;
                                                    });
                                                    summaryBtn.textContent =
                                                        `@lang('main.room'): ${roomCount}, @lang('main.adult'): ${adultsTotal}, @lang('main.child'): ${childrenTotal}`;
                                                }

                                                function reindexRooms() {
                                                    roomsContainer.querySelectorAll('.guest-room').forEach((r, i) => {
                                                        r.dataset.index = i;
                                                        r.querySelector('.room-number').textContent = i + 1;
                                                        r.querySelector('.input-adults').name = `rooms[${i}][adults]`;
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
                                                    roomsContainer.insertAdjacentHTML('beforeend',
                                                        tpl.replace(/__INDEX__/g, idx).replace(/__NUM__/g, num)
                                                    );
                                                    reindexRooms();
                                                }

                                                function updateRoomSummary(room) {
                                                    const aCount = +room.querySelector('.count-adult').textContent;
                                                    const cCount = +room.querySelector('.count-child').textContent;
                                                    const parts = [`${aCount} {{ __('main.adult') }}`];
                                                    if (cCount) parts.push(`${cCount} {{ __('main.child') }}`);
                                                    room.querySelector('.summary-text').textContent = parts.join(', ');
                                                    room.querySelector('.input-adults').value = aCount;
                                                    updateGlobalSummary();
                                                }

                                                summaryBtn.addEventListener('click', e => { e.preventDefault(); openPanel(); });
                                                closeBtn.addEventListener('click', e => { e.preventDefault(); closePanel(); });
                                                applyBtn.addEventListener('click', e => { e.preventDefault(); closePanel(); });
                                                overlay.addEventListener('click', closePanel);

                                                addRoomBtn.addEventListener('click', e => { e.preventDefault(); addRoom(); });

                                                document.addEventListener('click', e => {
                                                    if (!e.target.closest('.guest-room') &&
                                                        !e.target.closest('#rooms-summary') &&
                                                        !e.target.closest('#add-room')) return;

                                                    const room = e.target.closest('.guest-room');

                                                    if (e.target.closest('.remove-room')) { e.preventDefault(); room.remove(); reindexRooms(); return; }
                                                    if (e.target.closest('.guest-summary')) { e.preventDefault(); room.querySelector('.guest-dropdown').classList.toggle('hidden'); return; }
                                                    if (e.target.closest('.dec-adult')) { e.preventDefault(); const c = room.querySelector('.count-adult'); if (+c.textContent>1) c.textContent=+c.textContent-1; updateRoomSummary(room); return; }
                                                    if (e.target.closest('.inc-adult')) { e.preventDefault(); const c = room.querySelector('.count-adult'); if (+c.textContent<8) c.textContent=+c.textContent+1; updateRoomSummary(room); return; }
                                                    if (e.target.closest('.dec-child')) { e.preventDefault(); const c = room.querySelector('.count-child'); if (+c.textContent>0) c.textContent=+c.textContent-1; const wrap=room.querySelector('.children-ages'); if (wrap.lastElementChild) wrap.removeChild(wrap.lastElementChild); reindexRooms(); updateRoomSummary(room); return; }
                                                    if (e.target.closest('.inc-child')) { e.preventDefault(); const c = room.querySelector('.count-child'); if (+c.textContent<3){ c.textContent=+c.textContent+1; const wrap=room.querySelector('.children-ages'); const div=document.createElement('div'); div.className='flex items-center'; div.innerHTML=`<span class="mr-2 text-sm">@lang('main.age')</span>`; const sel=document.createElement('select'); sel.className='border border-gray-300 rounded-md px-2 py-1 text-sm'; for (let a=0;a<=18;a++) sel.insertAdjacentHTML('beforeend', `<option value="${a}">${a}</option>`); div.appendChild(sel); wrap.appendChild(div); reindexRooms(); updateRoomSummary(room);} return; }
                                                    if (e.target.closest('.apply-guests')) { e.preventDefault(); room.querySelector('.guest-dropdown').classList.add('hidden'); }
                                                });

                                                // первая комната
                                                addRoom();
                                            });
                                        </script>
                                    </div>
                                </div>

                                {{-- КНОПКА НАЙТИ --}}
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

        {{-- Блоки преимуществ/партнёров/подписки — без изменений --}}
        @if(app()->getLocale() == 'ru')
            {{-- ... ваш русский контент как в исходнике ... --}}
        @else
            {{-- ... ваш английский контент как в исходнике ... --}}
        @endif

    @else
        <script>window.location.href = "{{ route('extranet') }}";</script>
    @endauth
@endsection