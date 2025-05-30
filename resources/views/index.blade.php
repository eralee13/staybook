@extends('layouts.master')

@section('title', 'Главная страница')

@section('content')
    @auth
        <div class="main-filter">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>Остановитесь с удобством.
                            Работайте с выгодой.</h1>
                        <div class="type">
                            <div class="type-item current">
                                <a href="{{route('search')}}">Отели и номера</a>
                            </div>
                            <div class="type-item">
                                <a href="#">Трансфер</a>
                            </div>
                        </div>

                        <form action="{{ route('search') }}" method="GET">
                            <div class="row">
                                <div class="col-lg-3 col-md-12">
                                    <div class="form-group">
                                        <div class="label stay"><img src="{{route('index')}}/img/marker_out.svg" alt="">
                                        </div>
                                        <select name="city" id="city">
                                            @foreach($cities as $city)
                                                <option value="{{ $city->title }}">{{ $city->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg col-6">
                                    <div class="form-group">
                                        <div class="label in"><img src="{{route('index')}}/img/marker_in.svg" alt="">
                                            Заезд
                                        </div>
                                        <input type="text" id="date" class="date">
                                        <input type="hidden" id="arrivalDate" name="arrivalDate"
                                               value="{{ now()->format('Y-m-d') }}">
                                        <input type="hidden" id="departureDate" name="departureDate"
                                               value="{{ $tomorrow }}">
                                    </div>
                                </div>
                                <div class="col-lg col-6">
                                    {{-- Фильтр комнат --}}
                                    {{-- Общая сводка (клик открывает окно) --}}
                                    <a href="javascript:void(0)"
                                       id="rooms-summary"
                                       class="inline-block text-blue-600 hover:underline text-sm font-sans">
                                        Комнат: 1, Взрослых: 1, Детей: 0
                                    </a>

                                    {{-- Полупрозрачный оверлей --}}
                                    <div id="rooms-panel-overlay"
                                         class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

                                    {{-- Окно снизу --}}
                                    <div id="rooms-panel"
                                         class="fixed bottom-0 left-0 w-full bg-white border-t shadow-lg rounded-t-lg
            transform translate-y-full transition-transform duration-300
            max-h-[80vh] overflow-auto z-50">
                                        <div class="p-4">
                                            <div class="flex justify-between items-center mb-4">
                                                <h3 class="text-lg font-medium">Гости и номера</h3>
                                                <a href="javascript:void(0)"
                                                   id="panel-close"
                                                   class="text-gray-500 hover:text-gray-700 text-xl">&times;</a>
                                            </div>

                                            {{-- Кнопка добавить комнату --}}
                                            <a href="javascript:void(0)"
                                               id="add-room"
                                               class="inline-block text-blue-600 hover:underline text-sm mb-4">
                                                + Добавить номер
                                            </a>

                                            {{-- Сюда будут рендериться комнаты --}}
                                            <div id="rooms-container" class="space-y-4"></div>

                                            <div class="mt-4 text-right">
                                                <button id="panel-apply"
                                                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-md px-4 py-2 text-sm">
                                                    Готово
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Шаблон одной комнаты --}}
                                    <template id="room-template">
                                        <div class="guest-room bg-white border border-gray-200 rounded-md p-4 relative"
                                             data-index="__INDEX__">
                                            <h4 class="flex justify-between items-center text-sm font-medium mb-3">
                                                <span class="room-number">__NUM__</span> номер
                                                <a href="javascript:void(0)"
                                                   class="remove-room text-red-500 hover:text-red-700 text-xs ml-2">
                                                    Удалить
                                                </a>
                                            </h4>

                                            {{-- скрытое поле для взрослых --}}
                                            <input type="hidden"
                                                   name="rooms[__INDEX__][adults]"
                                                   value="1"
                                                   class="input-adults">

                                            {{-- сводка по комнате --}}
                                            <a href="javascript:void(0)"
                                               class="guest-summary flex justify-between items-center w-full border border-gray-300
              rounded-md px-4 py-2 bg-white text-sm hover:border-blue-500">
                                                <span class="summary-text">1 взрослый</span>
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
                                                <div class="flex items-center">
                                                    <button class="dec-adult">−</button>
                                                    <span class="count-adult mx-3 w-5 text-center text-sm">1</span>
                                                    <button class="inc-adult">+</button>
                                                </div>

                                                <div class="flex justify-between items-center mb-4">
                                                    <span class="text-sm">Дети</span>
                                                    <div class="flex items-center">
                                                        <button class="dec-child">−</button>
                                                        <span class="count-child mx-3 w-5 text-center text-sm">0</span>
                                                        <button class="inc-child">+</button>
                                                    </div>
                                                </div>
                                                <div class="children-ages space-y-2 mb-4"></div>
                                                <div class="text-right">
                                                    <button class="apply-guests inline-block bg-blue-600 hover:bg-blue-700 text-white
                       rounded-md px-4 py-2 text-sm">
                                                        Применить
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
                                            const tpl            = document.getElementById('room-template').innerHTML;
                                            let nextIndex        = 0;

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
                                                const roomCount   = rooms.length;
                                                let adultsTotal   = 0;
                                                let childrenTotal = 0;
                                                rooms.forEach(r => {
                                                    adultsTotal   += +r.querySelector('.count-adult').textContent;
                                                    childrenTotal += +r.querySelector('.count-child').textContent;
                                                });
                                                summaryBtn.textContent =
                                                    `Комнат: ${roomCount}, Взрослых: ${adultsTotal}, Детей: ${childrenTotal}`;
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
                                                const parts = [`${aCount} ${aCount===1?'взрослый':'взрослых'}`];
                                                if (cCount) parts.push(`${cCount} ${cCount===1?'ребёнок':'детей'}`);
                                                room.querySelector('.summary-text').textContent = parts.join(', ');
                                                room.querySelector('.input-adults').value = aCount;
                                                updateGlobalSummary();
                                            }

                                            // Открытие/закрытие
                                            summaryBtn.addEventListener('click', e => { e.preventDefault(); openPanel(); });
                                            closeBtn  .addEventListener('click', e => { e.preventDefault(); closePanel(); });
                                            applyBtn  .addEventListener('click', e => { e.preventDefault(); closePanel(); });
                                            overlay   .addEventListener('click',     closePanel);

                                            // Добавить комнату
                                            addRoomBtn.addEventListener('click', e => { e.preventDefault(); addRoom(); });

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
                                                    e.preventDefault(); room.remove(); reindexRooms(); return;
                                                }
                                                if (e.target.closest('.guest-summary')) {
                                                    e.preventDefault(); room.querySelector('.guest-dropdown').classList.toggle('hidden'); return;
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
                                                    reindexRooms(); updateRoomSummary(room);
                                                    return;
                                                }
                                                if (e.target.closest('.inc-child')) {
                                                    e.preventDefault();
                                                    const cnt = room.querySelector('.count-child');
                                                    if (+cnt.textContent < 3) {
                                                        cnt.textContent = +cnt.textContent + 1;
                                                        // создаём select для возраста
                                                        const wrap = room.querySelector('.children-ages');
                                                        const div  = document.createElement('div');
                                                        div.className = 'flex items-center';
                                                        div.innerHTML = `<span class="mr-2 text-sm">Возраст</span>`;
                                                        const sel = document.createElement('select');
                                                        sel.className = 'border border-gray-300 rounded-md px-2 py-1 text-sm';
                                                        for (let a=0; a<=18; a++) sel.insertAdjacentHTML('beforeend', `<option value="${a}">${a}</option>`);
                                                        div.appendChild(sel);
                                                        wrap.appendChild(div);
                                                        reindexRooms(); updateRoomSummary(room);
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

                                    <style>
                                        /* Оверлей */
                                        #rooms-panel-overlay {
                                            position: fixed;
                                            inset: 0;
                                            background: rgba(0, 0, 0, 0.5);
                                            z-index: 40;
                                            display: none;
                                        }

                                        #rooms-panel-overlay.open {
                                            display: block;
                                        }

                                        #rooms-container svg{
                                            max-width: 20px;
                                        }


                                        /* Панель снизу */
                                        #rooms-panel {
                                            position: fixed;
                                            bottom: 0;
                                            left: 0;
                                            width: 100%;
                                            max-height: 80vh;
                                            background: #ffffff;
                                            border-top: 1px solid #e5e7eb;
                                            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
                                            border-radius: 0.75rem 0.75rem 0 0;
                                            transform: translateY(100%);
                                            transition: transform 0.3s ease-in-out;
                                            overflow-y: auto;
                                            z-index: 50;
                                        }

                                        #rooms-panel.open {
                                            transform: translateY(0);
                                        }

                                        /* Сводка по комнатам */
                                        #rooms-summary {
                                            display: inline-flex;
                                            align-items: center;
                                            padding: 0.5rem 1rem;
                                            border: 1px solid #d1d5db;
                                            border-radius: 0.375rem;
                                            background: #ffffff;
                                            font-size: 0.875rem;
                                            color: #1f2937;
                                            cursor: pointer;
                                            user-select: none;
                                        }

                                        #rooms-summary.opacity-50 {
                                            opacity: .5;
                                            pointer-events: none;
                                        }

                                        #rooms-summary:hover {
                                            border-color: #3b82f6;
                                            color: #1e40af;
                                        }

                                        /* Одна комната в списке */
                                        .guest-room {
                                            background: #ffffff;
                                            border: 1px solid #e5e7eb;
                                            border-radius: 0.5rem;
                                            padding: 1rem;
                                            position: relative;
                                        }

                                        .guest-room .remove-room {
                                            font-size: 0.75rem;
                                            color: #ef4444;
                                            cursor: pointer;
                                        }

                                        .guest-room .remove-room:hover {
                                            color: #b91c1c;
                                        }

                                        /* Сводка по одной комнате */
                                        .guest-summary {
                                            display: flex;
                                            justify-content: space-between;
                                            align-items: center;
                                            padding: 0.5rem 1rem;
                                            border: 1px solid #d1d5db;
                                            border-radius: 0.375rem;
                                            background: #ffffff;
                                            font-size: 0.875rem;
                                            color: #1f2937;
                                            cursor: pointer;
                                        }

                                        .guest-summary:hover {
                                            border-color: #3b82f6;
                                        }

                                        .guest-dropdown {
                                            position: absolute;
                                            top: 100%;
                                            left: 0;
                                            width: 100%;
                                            background: #ffffff;
                                            border: 1px solid #e5e7eb;
                                            border-radius: 0.5rem;
                                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                                            margin-top: 0.5rem;
                                            padding: 1rem;
                                            z-index: 60;
                                        }

                                        .guest-dropdown.open {
                                            display: block;
                                        }

                                        /* Кнопки «−» и «+» */
                                        .dec-adult, .inc-adult, .dec-child, .inc-child {
                                            width: 1.5rem;
                                            height: 1.5rem;
                                            border: 1px solid #d1d5db;
                                            border-radius: 9999px;
                                            display: inline-flex;
                                            align-items: center;
                                            justify-content: center;
                                            font-size: 1rem;
                                            color: #1f2937;
                                            cursor: pointer;
                                            user-select: none;
                                        }

                                        .dec-adult:hover, .inc-adult:hover,
                                        .dec-child:hover, .inc-child:hover {
                                            border-color: #3b82f6;
                                            color: #3b82f6;
                                        }

                                        /* Счётчики */
                                        .count-adult, .count-child {
                                            width: 1.25rem;
                                            text-align: center;
                                            font-size: 0.875rem;
                                            color: #1f2937;
                                        }

                                        /* Селект возраста */
                                        .children-ages select {
                                            border: 1px solid #d1d5db;
                                            border-radius: 0.375rem;
                                            padding: 0.25rem 0.5rem;
                                            font-size: 0.875rem;
                                            color: #1f2937;
                                        }

                                        /* Кнопка «Готово» */
                                        .apply-guests {
                                            display: inline-block;
                                            padding: 0.5rem 1rem;
                                            border-radius: 0.375rem;
                                            background: #3b82f6;
                                            color: #ffffff;
                                            font-size: 0.875rem;
                                            cursor: pointer;
                                            user-select: none;
                                        }

                                        .apply-guests:hover {
                                            background: #2563eb;
                                        }

                                        /* Гарантируем видимость кнопок −/+ */
                                        .dec-adult,
                                        .inc-adult,
                                        .dec-child,
                                        .inc-child {
                                            display: inline-flex !important;
                                            align-items: center;
                                            justify-content: center;
                                            width: 1.5rem;
                                            height: 1.5rem;
                                            border: 1px solid #d1d5db;        /* серый контур */
                                            border-radius: 0.375rem;           /* rounded-md */
                                            background-color: #ffffff;         /* белый фон */
                                            color: #374151;                    /* тёмно-серый текст */
                                            font-size: 1rem;                   /* чтобы минус/плюс были читаемы */
                                            line-height: 1;
                                            cursor: pointer;
                                        }

                                        /* При наведении подчёркиваем рамку и цвет текста */
                                        .dec-adult:hover,
                                        .inc-adult:hover,
                                        .dec-child:hover,
                                        .inc-child:hover {
                                            border-color: #3b82f6;             /* blue-500 */
                                            color: #3b82f6;
                                        }

                                        /* Уберём любое «display: none» или «opacity: 0» */
                                        .dec-adult,
                                        .inc-adult,
                                        .dec-child,
                                        .inc-child {
                                            opacity: 1 !important;
                                            visibility: visible !important;
                                        }
                                    </style>


                                </div>

                                <div class="col-lg col-6 extra">
                                    <div class="form-group">
                                        <div id="filter">
                                            <div class="label filter"><img src="{{route('index')}}/img/setting.svg"
                                                                           alt="">
                                                Фильтры
                                            </div>
                                            <div class="filter-wrap" id="filter-wrap">
                                                <div class="closebtn" id="closebtn"><img
                                                            src="{{route('index')}}/img/close.svg" alt=""></div>
                                                <h5>Фильтры</h5>
                                                <div class="form-group">
                                                    <div class="name">Рейтинг</div>
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
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <div class="apart-item">
                                                                <img src="{{route('index')}}/img/hotelb.svg" alt="">
                                                                <h6>Отели</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="line"></div>
                                                <div class="name">Прибытие</div>
                                                <div class="form-group" id="income">
                                                    <div class="row">
                                                        <div class="col-md-6 col-6">
                                                            <div class="itemm">
                                                                <input type="checkbox" value="early_in">
                                                                <label for="">Ранний заезд</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-6">
                                                            <div class="itemm">
                                                                <input type="checkbox" value="late_out">
                                                                <label for="">Поздний выезд</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="line"></div>
                                                <div class="form-group" id="meal">
                                                    <div class="name">Виды питания</div>
                                                    <div class="row justify-content-center">
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm">
                                                                <input type="radio" name="meal_id" value="1">
                                                                <label for="">RO</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm">
                                                                <input type="radio" name="meal_id" value="2">
                                                                <label for="">BB</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm">
                                                                <input type="radio" name="meal_id" value="3">
                                                                <label for="">HB</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm">
                                                                <input type="radio" name="meal_id" value="4">
                                                                <label for="">FB</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm">
                                                                <input type="radio" name="meal_id" value="5">
                                                                <label for="">AI</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{--                                            <button type="submit" class="more">Найти</button>--}}
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-lg col-12">
                                    <div class="form-group">
                                        <button type="submit" class="more"><img src="{{route('index')}}/img/search.svg"
                                                                                alt=""> Найти
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="property-list">
                            <div class="property-list-item">
                                <a href="#">
                                    <img src="{{route('index')}}/img/hotel.svg" alt="">
                                    <div class="name">Отели</div>
                                </a>
                            </div>
                            <div class="property-list-item">
                                <a href="#">
                                    <img src="{{route('index')}}/img/rooms.svg" alt="">
                                    <div class="name">Номера</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="places">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Места в Кыргызстане</h2>
                    </div>
                </div>
                <div class="row">
                    @foreach($hotels as $hotel)
                        <div class="col-lg-4 col-md-6">
                            <div class="places-item">
                                    <span class="img-wrap">
                                        <img src="{{ Storage::url($hotel->image) }}" alt="">
                                    </span>
                                <div class="text-wrap">
                                    <div class="address">{{ $hotel->city }}</div>
                                    <h5>{{ $hotel->title }}</h5>
                                    <div class="rating"><img src="{{ route('index') }}/img/star.svg"
                                                             alt=""> {{ $hotel->rating }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-wrap">
                            <a href="{{ route('hotels') }}">Показать больше</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="places popular">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Популярные направления</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="places-item">
                            <a href="#"><img src="img/place1.jpg" alt=""></a>
                            <div class="text-wrap">
                                <div class="address">Бишкек, Кыргызстан</div>
                                <div class="rating"><img src="img/star.svg" alt=""> 4,76</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="places-item">
                            <a href="#"><img src="img/place2.jpg" alt=""></a>
                            <div class="text-wrap">
                                <div class="address">Бишкек, Кыргызстан</div>
                                <div class="rating"><img src="img/star.svg" alt=""> 4,76</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="places-item">
                            <a href="#"><img src="img/place3.jpg" alt=""></a>
                            <div class="text-wrap">
                                <div class="address">Бишкек, Кыргызстан</div>
                                <div class="rating"><img src="img/star.svg" alt=""> 4,76</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="places-item">
                            <a href="#"><img src="img/place1.jpg" alt=""></a>
                            <div class="text-wrap">
                                <div class="address">Бишкек, Кыргызстан</div>
                                <div class="rating"><img src="img/star.svg" alt=""> 4,76</div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-wrap">
                            <a href="{{ route('hotels') }}">Показать больше</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @include('layouts.auth')
    @endauth
@endsection
