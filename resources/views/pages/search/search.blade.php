@extends('layouts.main')

@php
    use App\Models\Image;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Storage;

    // Безопасные даты
    $arrRaw = request('arrivalDate');
    $depRaw = request('departureDate');
    $arr = $arrRaw ? Carbon::parse($arrRaw)->format('d.m.Y') : '';
    $dep = $depRaw ? Carbon::parse($depRaw)->format('d.m.Y') : '';

    // Гости/комнаты
    $roomsData = request()->input('rooms', []);
    $roomCount = count($roomsData);
    $totalAdults = 0;
    $totalChildren = 0;
    foreach ($roomsData as $r) {
        $totalAdults  += (int)($r['adults'] ?? 0);
        $totalChildren += count($r['childAges'] ?? []);
    }
@endphp

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
                                {{-- Поиск + подсказки --}}
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group" style="position:relative">
                                        <input
                                                type="text"
                                                id="searchbox"
                                                name="city"
                                                placeholder="@lang('main.city_or_hotel')"
                                                autocomplete="off"
                                                value="{{ request('city') }}"
                                                required
                                        >
                                        <img src="{{ route('index') }}/img/arrow_down.svg" alt="">
                                        <div id="suggest" class="suggest hidden"></div>
                                        <input type="hidden" name="city_id" id="city_id">

                                        <style>
                                            .suggest{position:absolute;z-index:9999;background:#fff;border:1px solid #e5e7eb;width:100%;max-height:280px;overflow:auto;border-radius:8px;box-shadow:0 10px 20px rgba(0,0,0,.08)}
                                            .suggest.hidden{display:none}
                                            .suggest-item{padding:10px 12px;cursor:pointer;display:flex;gap:8px;align-items:center}
                                            .suggest-item:hover,.suggest-item.active{background:#f3f4f6}
                                            .s-title{font-weight:600;font-size:14px}
                                            .s-sub{font-size:12px;color:#6b7280}
                                            .s-badge{font-size:11px;color:#111827;background:#fef3c7;border:1px solid #fcd34d;border-radius:6px;padding:2px 6px}
                                        </style>

                                        <script>
                                            document.addEventListener('DOMContentLoaded', () => {
                                                const input = document.getElementById('searchbox');
                                                const box   = document.getElementById('suggest');
                                                const url   = @json(route('search.suggest'));
                                                let items = [];
                                                let activeIdx = -1;
                                                let lastQuery = '';
                                                let t = null;

                                                const debounce = (fn, ms) => (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
                                                const hide = () => { box.classList.add('hidden'); activeIdx = -1; };
                                                const show = () => { box.classList.remove('hidden'); };

                                                function render(list){
                                                    if(!list.length){ hide(); return; }
                                                    box.innerHTML = list.map((it, i) => {
                                                        const rating = it.rating ? `<span class="s-badge">★ ${it.rating}</span>` : '';
                                                        const type = it.type === 'hotel' ? '<span class="s-badge">Отель</span>' : '<span class="s-badge">Город</span>';
                                                        const alt = it.alt && it.alt !== it.label ? ` · <span class="s-sub">${it.alt}</span>` : '';
                                                        const city = it.city ? `<div class="s-sub">${it.city}</div>` : '';
                                                        return `<div class="suggest-item" data-idx="${i}">
                                                            <div><div class="s-title">${it.label} ${rating}${alt} ${type}</div>${city}</div>
                                                        </div>`;
                                                    }).join('');
                                                    show();
                                                }

                                                async function fetchSuggest(q){
                                                    try{
                                                        const resp = await fetch(url + '?q=' + encodeURIComponent(q), { headers:{'Accept':'application/json'}, cache:'no-store' });
                                                        const ct = resp.headers.get('content-type') || '';
                                                        if(!resp.ok || !ct.includes('application/json')){ hide(); return; }
                                                        const data = await resp.json();
                                                        items = Array.isArray(data.items) ? data.items : [];
                                                        render(items);
                                                    }catch{ hide(); }
                                                }

                                                const onType = debounce(e => {
                                                    const q = (e.target.value || '').trim();
                                                    if(q.length < 2){ hide(); lastQuery=''; return; }
                                                    if(q === lastQuery) return;
                                                    lastQuery = q;
                                                    fetchSuggest(q);
                                                }, 250);

                                                input.addEventListener('input', onType);
                                                input.addEventListener('focus', () => { if((input.value||'').trim().length >= 2 && items.length) show(); });
                                                input.addEventListener('blur', () => setTimeout(hide, 150));

                                                box.addEventListener('click', (e) => {
                                                    const el = e.target.closest('.suggest-item');
                                                    if(!el) return;
                                                    const it = items[+el.dataset.idx];
                                                    if(!it) return;
                                                    input.value = it.label;
                                                    if(it.city_id){ const cityIdEl = document.getElementById('city_id'); if(cityIdEl) cityIdEl.value = it.city_id; }
                                                    hide();
                                                });

                                                input.addEventListener('keydown', (e) => {
                                                    if(box.classList.contains('hidden')) return;
                                                    if(e.key === 'ArrowDown'){ e.preventDefault(); activeIdx = (activeIdx + 1) % items.length; highlight(); }
                                                    else if(e.key === 'ArrowUp'){ e.preventDefault(); activeIdx = (activeIdx - 1 + items.length) % items.length; highlight(); }
                                                    else if(e.key === 'Enter'){
                                                        if(activeIdx >= 0 && items[activeIdx]){
                                                            e.preventDefault();
                                                            const it = items[activeIdx];
                                                            input.value = it.label;
                                                            if(it.city_id){ const cityIdEl = document.getElementById('city_id'); if(cityIdEl) cityIdEl.value = it.city_id; }
                                                            hide();
                                                        }
                                                    }else if(e.key === 'Escape'){ hide(); }
                                                });

                                                function highlight(){
                                                    [...box.querySelectorAll('.suggest-item')].forEach((el, i) => {
                                                        el.classList.toggle('active', i === activeIdx);
                                                        if(i === activeIdx) el.scrollIntoView({block:'nearest'});
                                                    });
                                                }
                                            });
                                        </script>
                                    </div>
                                </div>

                                {{-- Даты --}}
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <input type="text" id="arrivalDisplay" class="date" autocomplete="off" value="{{ $arr }}">
                                        <input type="hidden" id="arrivalDate" name="arrivalDate" value="{{ request('arrivalDate') }}">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <input type="text" id="departureDisplay" class="date" autocomplete="off" value="{{ $dep }}">
                                        <input type="hidden" id="departureDate" name="departureDate" value="{{ request('departureDate') }}">
                                    </div>
                                </div>

                                {{-- Фильтры --}}
                                <div class="col-lg-4 col-md-6 extra">
                                    <div class="form-group">
                                        <img src="{{ route('index') }}/img/filter.svg" style="top: 12px">
                                        <div id="filter">
                                            <div class="label filter">@lang('main.filters')</div>
                                            <div class="filter-wrap" id="filter-wrap">
                                                <div class="closebtn" id="closebtn"><img src="{{route('index')}}/img/close_btn.svg" alt=""></div>
                                                <h5>@lang('main.filters')</h5>

                                                <div class="form-group">
                                                    <div class="name">@lang('main.rating')</div>
                                                    <div class="row justify-content-center">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <div class="col">
                                                                <div class="item">
                                                                    <input class="rating-input" type="radio" name="rating" id="rating-{{ $i }}" value="{{ $i }}" {{ (int)request('rating') === $i ? 'checked' : '' }}>
                                                                    <label class="img {{ (int)request('rating') === $i ? 'active' : '' }}" for="rating-{{ $i }}">
                                                                        <div class="num">{{ $i }}</div>
                                                                        <div class="img-wrap">
                                                                            <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M16.3808 4.30595C16.5459 4.02269 16.7824 3.78766 17.0666 3.62431C17.3509 3.46096 17.673 3.375 18.0008 3.375C18.3287 3.375 18.6508 3.46096 18.9351 3.62431C19.2193 3.78766 19.4558 4.02269 19.6208 4.30595L23.8133 11.503L31.9553 13.267C32.2756 13.3365 32.572 13.4889 32.8151 13.7088C33.0581 13.9286 33.2393 14.2084 33.3405 14.5201C33.4417 14.8318 33.4595 15.1646 33.392 15.4853C33.3245 15.8061 33.1741 16.1035 32.9558 16.348L27.4058 22.5595L28.2458 30.847C28.279 31.1733 28.2259 31.5026 28.092 31.802C27.958 32.1014 27.7479 32.3605 27.4825 32.5533C27.2172 32.7461 26.9059 32.8659 26.5797 32.9008C26.2535 32.9356 25.924 32.8843 25.6238 32.752L18.0008 29.392L10.3778 32.752C10.0777 32.8843 9.74812 32.9356 9.42196 32.9008C9.09581 32.8659 8.78451 32.7461 8.51914 32.5533C8.25377 32.3605 8.04363 32.1014 7.90969 31.802C7.77574 31.5026 7.72269 31.1733 7.75583 30.847L8.59583 22.5595L3.04583 16.3495C2.82717 16.105 2.67647 15.8074 2.60875 15.4865C2.54104 15.1656 2.55869 14.8325 2.65995 14.5206C2.7612 14.2086 2.94252 13.9287 3.18579 13.7087C3.42906 13.4887 3.72579 13.3364 4.04633 13.267L12.1883 11.503L16.3808 4.30595ZM18.0008 7.48445L14.5313 13.4425C14.4001 13.6673 14.2236 13.8624 14.0128 14.0153C13.8021 14.1682 13.5618 14.2755 13.3073 14.3305L6.56933 15.79L11.1623 20.9305C11.5133 21.3235 11.6828 21.8455 11.6303 22.369L10.9358 29.2285L17.2448 26.4475C17.4831 26.3425 17.7405 26.2883 18.0008 26.2883C18.2611 26.2883 18.5186 26.3425 18.7568 26.4475L25.0658 29.2285L24.3713 22.369C24.345 22.1099 24.3728 21.8483 24.4531 21.6006C24.5334 21.3529 24.6645 21.1247 24.8378 20.9305L29.4323 15.79L22.6943 14.3305C22.4398 14.2755 22.1996 14.1682 21.9888 14.0153C21.7781 13.8624 21.6015 13.6673 21.4703 13.4425L18.0008 7.48445Z" fill="black"/>
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
                                                        <div class="row">
                                                            @php $meals = \App\Models\Meal::all(); @endphp
                                                            @foreach ($meals as $meal)
                                                                <div class="col">
                                                                    <div class="itemmm {{ in_array($meal->id, (array) request('meal')) ? 'active' : '' }}">
                                                                        <input type="checkbox" name="meal[]" id="{{ $meal->code }}" value="{{ $meal->id }}" {{ in_array($meal->id, (array) request('meal')) ? 'checked' : '' }}>
                                                                        <label class="meal-checkbox" for="{{ $meal->code }}">{{ $meal->code }}</label>
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

                                {{-- Гости/комнаты --}}
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <a href="javascript:void(0)" id="rooms-summary">
                                            @lang('main.room'): 1, @lang('main.adult'): 1, @lang('main.child'): 0
                                        </a>

                                        <div id="rooms-panel-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

                                        <div id="rooms-panel">
                                            <h5>@lang('main.guests_and_rooms')</h5>
                                            <div class="close-btn">
                                                <a href="javascript:void(0)" id="panel-close"><img src="{{ route('index') }}/img/close_btn.svg" alt=""></a>
                                            </div>
                                            <div class="add-btn">
                                                <a href="javascript:void(0)" class="more" id="add-room">@lang('main.add_room')</a>
                                            </div>

                                            <div id="rooms-container" class="space-y-4"></div>

                                            <div class="mt-4 text-right">
                                                <button id="panel-apply" class="more">@lang('main.ready')</button>
                                            </div>
                                        </div>

                                        <template id="room-template">
                                            <div class="guest-room" data-index="__INDEX__">
                                                <div class="row">
                                                    <div class="col-md-6 col-6">
                                                        <h4 class="flex justify-between items-center text-sm font-medium mb-3">
                                                            <span class="room-number">__NUM__</span> @lang('main.room')
                                                        </h4>
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

                                                <a href="javascript:void(0)" class="guest-summary flex justify-between items-center w-full border border-gray-300 rounded-md px-4 py-2 bg-white text-sm hover:border-blue-500">
                                                    <span class="summary-text">1 @lang('main.adult')</span>
                                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </a>

                                                <div class="guest-dropdown hidden absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg p-4">
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
                                                const summaryBtn     = document.getElementById('rooms-summary');
                                                const overlay        = document.getElementById('rooms-panel-overlay');
                                                const panel          = document.getElementById('rooms-panel');
                                                const closeBtn       = document.getElementById('panel-close');
                                                const applyBtn       = document.getElementById('panel-apply');
                                                const addRoomBtn     = document.getElementById('add-room');
                                                const roomsContainer = document.getElementById('rooms-container');
                                                const tpl            = document.getElementById('room-template').innerHTML;
                                                let nextIndex = 0;

                                                const openPanel  = () => { overlay.classList.remove('hidden'); panel.classList.add('open'); };
                                                const closePanel = () => { panel.classList.remove('open'); overlay.classList.add('hidden'); };

                                                function updateGlobalSummary() {
                                                    const rooms = roomsContainer.querySelectorAll('.guest-room');
                                                    const roomCount = rooms.length || 1;
                                                    let adultsTotal = 0;
                                                    let childrenTotal = 0;
                                                    rooms.forEach(r => {
                                                        adultsTotal  += +r.querySelector('.count-adult').textContent;
                                                        childrenTotal += +r.querySelector('.count-child').textContent;
                                                    });
                                                    summaryBtn.textContent = `@lang('main.room'): ${roomCount}, @lang('main.adult'): ${adultsTotal || 1}, @lang('main.child'): ${childrenTotal || 0}`;
                                                    summaryBtn.classList.toggle('opacity-50', roomCount >= MAX_ROOMS);
                                                    summaryBtn.classList.toggle('pointer-events-none', roomCount >= MAX_ROOMS);
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
                                                    roomsContainer.insertAdjacentHTML('beforeend', tpl.replace(/__INDEX__/g, idx).replace(/__NUM__/g, num));
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
                                                closeBtn.addEventListener('click',  e => { e.preventDefault(); closePanel(); });
                                                applyBtn.addEventListener('click',  e => { e.preventDefault(); closePanel(); });
                                                overlay.addEventListener('click', closePanel);

                                                addRoomBtn.addEventListener('click', e => { e.preventDefault(); addRoom(); });

                                                document.addEventListener('click', e => {
                                                    if (!e.target.closest('.guest-room') && !e.target.closest('#rooms-summary') && !e.target.closest('#add-room')) return;
                                                    const room = e.target.closest('.guest-room');

                                                    if (e.target.closest('.remove-room')) { e.preventDefault(); room.remove(); reindexRooms(); return; }
                                                    if (e.target.closest('.guest-summary')) { e.preventDefault(); room.querySelector('.guest-dropdown').classList.toggle('hidden'); return; }
                                                    if (e.target.closest('.dec-adult')) { e.preventDefault(); const cnt = room.querySelector('.count-adult'); if (+cnt.textContent > 1) cnt.textContent = +cnt.textContent - 1; updateRoomSummary(room); return; }
                                                    if (e.target.closest('.inc-adult')) { e.preventDefault(); const cnt = room.querySelector('.count-adult'); if (+cnt.textContent < 8) cnt.textContent = +cnt.textContent + 1; updateRoomSummary(room); return; }
                                                    if (e.target.closest('.dec-child')) { e.preventDefault(); const cnt = room.querySelector('.count-child'); if (+cnt.textContent > 0) cnt.textContent = +cnt.textContent - 1; const wrap = room.querySelector('.children-ages'); if (wrap.lastElementChild) wrap.removeChild(wrap.lastElementChild); reindexRooms(); updateRoomSummary(room); return; }
                                                    if (e.target.closest('.inc-child')) { e.preventDefault(); const cnt = room.querySelector('.count-child'); if (+cnt.textContent < 3) { cnt.textContent = +cnt.textContent + 1; const wrap = room.querySelector('.children-ages'); const div = document.createElement('div'); div.className = 'flex items-center'; div.innerHTML = `<span class="mr-2 text-sm">@lang('main.age')</span>`; const sel = document.createElement('select'); sel.className = 'border border-gray-300 rounded-md px-2 py-1 text-sm'; for (let a = 0; a <= 18; a++) sel.insertAdjacentHTML('beforeend', `<option value="${a}">${a}</option>`); div.appendChild(sel); wrap.appendChild(div); reindexRooms(); updateRoomSummary(room); } return; }
                                                    if (e.target.closest('.apply-guests')) { e.preventDefault(); room.querySelector('.guest-dropdown').classList.add('hidden'); }
                                                });

                                                // init
                                                addRoom();
                                            });
                                        </script>
                                    </div>
                                </div>

                                {{-- Найти --}}
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        @csrf
                                        <button type="submit" class="more">@lang('main.find')</button>
                                    </div>
                                </div>
                            </div> {{-- /row --}}
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </div>

        {{-- Результаты + Карта --}}
        <div class="page search" style="margin-bottom: 60px">
            <div class="container-fluid">
                <div class="row">
                    {{-- Листинг --}}
                    <div class="col-lg-7 col-md-12">
                        <div id="hotel-list">
                            @if($allHotels->isEmpty())
                                <div class="alert alert-danger">@lang('main.not_hotel')</div>
                                <div class="btn-wrap"><a href="{{ route('index') }}">@lang('main.try_again')</a></div>
                            @else
                                <script>const suggestUrl = @json(route('search.suggest'));</script>

                                @foreach($allHotels as $item)
                                    @php
                                        $source = $item['source'] ?? '';
                                    @endphp

                                    {{-- LOCAL --}}
                                    @if($source === 'local')
                                        @include('pages.search.parts.local', ['item' => $item, 'roomCount' => $roomCount, 'totalAdults' => $totalAdults, 'totalChildren' => $totalChildren])
                                    @elseif($source === 'exely')
                                        @include('pages.search.parts.exely', ['item' => $item, 'roomCount' => $roomCount, 'totalAdults' => $totalAdults, 'totalChildren' => $totalChildren])

                                        {{-- TOURMIND --}}
                                    @elseif($source === 'tm')
                                        @include('pages.search.parts.tourmind', ['item' => $item, 'roomCount' => $roomCount, 'totalAdults' => $totalAdults, 'totalChildren' => $totalChildren])
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
                                    $rawHotel = $item['hotel'] ?? null;
                                    $h = is_object($rawHotel) ? $rawHotel : (is_array($rawHotel) ? (object)$rawHotel : null);
                                    $raw = $item[$item['source']] ?? null; // tm | roomStay и т.д.
                                    $id = $h->id ?? ($raw->hid ?? ($raw->propertyId ?? null));
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
                                ->filter(fn ($i) => !empty($i['lat']) && !empty($i['lng']) && !empty($i['id']))
                                ->values();
                        @endphp

                        {{-- Leaflet --}}
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
                                    Object.values(listItems).forEach(el => el && el.classList.remove('active'));
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

    @else
        @include('layouts.auth')
    @endauth
@endsection