@extends('layouts.master')

@section('title', 'Главная страница')

@section('content')
    @auth
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

        <style>
            .select2-container--default .select2-selection--single {
                height: 50px;
                line-height: 50px;
                display: block;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 50px;
            }
        </style>
        

        <div class="main-filter">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>@lang('main.head')</h1>
                        <div class="type">
                            <div class="type-item current">
                                <a href="{{route('search')}}">@lang('main.hotels_and_rooms')</a>
                            </div>
                            <div class="type-item">
                                <a href="{{ route('offline') }}">@lang('main.offline')</a>
                            </div>
                            {{--                            <div class="type-item">--}}
                            {{--                                <a href="#">@lang('main.transfer')</a>--}}
                            {{--                            </div>--}}
                        </div>

                        <form action="{{ route('search') }}" method="GET">
                            <div class="row">
                                <div class="col-lg-3 col-md-12">
                                    <div class="form-group">
                                        <div class="label stay"><img src="{{route('index')}}/img/marker_out.svg" alt="">
                                        </div>
                                        <select name="city" id="city">
                                            <option value="3421-Tegucigalpa">Tegucigalpa</option>
                                            <option value="1259">Moscow</option>
                                            <option value="67005-Moscow">Moscow</option>
                                            <option value="822-China">china</option>
                                            <option value="2395-Moscow">Moscow</option>
                                            <option value="378-Amsterdam">Amsterdam</option>
                                            <option value="6384-Amsterdam">Amsterdam</option>
                                            <option value="602645-Amsterdam">Amsterdam</option>
                                            <option value="3421-Kyiv">Kyiv</option>
                                            {{-- @foreach($cities as $city)
                                                <option value="{{ $city->country_id }}-{{ $city->name }}">{{ $city->name }}</option>
                                            @endforeach --}}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg col-6">
                                    <div class="form-group">
                                        <select id="nationality" name="nationality">
                                            <option value="">Гражданство</option>
                                            <option value="AF">Afghanistan</option>
                                            <option value="AL">Albania</option>
                                            <option value="DZ">Algeria</option>
                                            <option value="AS">American Samoa</option>
                                            <option value="AD">Andorra</option>
                                            <option value="AO">Angola</option>
                                            <option value="AI">Anguilla</option>
                                            <option value="AQ">Antarctica</option>
                                            <option value="AG">Antigua and Barbuda</option>
                                            <option value="AR">Argentina</option>
                                            <option value="AM">Armenia</option>
                                            <option value="AU">Australia</option>
                                            <option value="AT">Austria</option>
                                            <option value="AZ">Azerbaijan</option>
                                            <option value="BS">Bahamas</option>
                                            <option value="BH">Bahrain</option>
                                            <option value="BD">Bangladesh</option>
                                            <option value="BB">Barbados</option>
                                            <option value="BY">Belarus</option>
                                            <option value="BE">Belgium</option>
                                            <option value="BZ">Belize</option>
                                            <option value="BJ">Benin</option>
                                            <option value="BM">Bermuda</option>
                                            <option value="BT">Bhutan</option>
                                            <option value="BO">Bolivia</option>
                                            <option value="BA">Bosnia and Herzegovina</option>
                                            <option value="BW">Botswana</option>
                                            <option value="BR">Brazil</option>
                                            <option value="BN">Brunei Darussalam</option>
                                            <option value="BG">Bulgaria</option>
                                            <option value="BF">Burkina Faso</option>
                                            <option value="BI">Burundi</option>
                                            <option value="KH">Cambodia</option>
                                            <option value="CM">Cameroon</option>
                                            <option value="CA">Canada</option>
                                            <option value="CV">Cape Verde</option>
                                            <option value="KY">Cayman Islands</option>
                                            <option value="CF">Central African Republic</option>
                                            <option value="TD">Chad</option>
                                            <option value="CL">Chile</option>
                                            <option value="CN">China</option>
                                            <option value="CX">Christmas Island</option>
                                            <option value="CC">Cocos (Keeling) Islands</option>
                                            <option value="CO">Colombia</option>
                                            <option value="KM">Comoros</option>
                                            <option value="CG">Congo</option>
                                            <option value="CD">Congo, Democratic Republic</option>
                                            <option value="CK">Cook Islands</option>
                                            <option value="CR">Costa Rica</option>
                                            <option value="CI">Côte d’Ivoire</option>
                                            <option value="HR">Croatia</option>
                                            <option value="CU">Cuba</option>
                                            <option value="CY">Cyprus</option>
                                            <option value="CZ">Czech Republic</option>
                                            <option value="DK">Denmark</option>
                                            <option value="DJ">Djibouti</option>
                                            <option value="DM">Dominica</option>
                                            <option value="DO">Dominican Republic</option>
                                            <option value="EC">Ecuador</option>
                                            <option value="EG">Egypt</option>
                                            <option value="SV">El Salvador</option>
                                            <option value="GQ">Equatorial Guinea</option>
                                            <option value="ER">Eritrea</option>
                                            <option value="EE">Estonia</option>
                                            <option value="ET">Ethiopia</option>
                                            <option value="FJ">Fiji</option>
                                            <option value="FI">Finland</option>
                                            <option value="FR">France</option>
                                            <option value="GA">Gabon</option>
                                            <option value="GM">Gambia</option>
                                            <option value="GE">Georgia</option>
                                            <option value="DE">Germany</option>
                                            <option value="GH">Ghana</option>
                                            <option value="GR">Greece</option>
                                            <option value="GD">Grenada</option>
                                            <option value="GT">Guatemala</option>
                                            <option value="GN">Guinea</option>
                                            <option value="GW">Guinea-Bissau</option>
                                            <option value="GY">Guyana</option>
                                            <option value="HT">Haiti</option>
                                            <option value="HN">Honduras</option>
                                            <option value="HU">Hungary</option>
                                            <option value="IS">Iceland</option>
                                            <option value="IN">India</option>
                                            <option value="ID">Indonesia</option>
                                            <option value="IR">Iran</option>
                                            <option value="IQ">Iraq</option>
                                            <option value="IE">Ireland</option>
                                            <option value="IL">Israel</option>
                                            <option value="IT">Italy</option>
                                            <option value="JM">Jamaica</option>
                                            <option value="JP">Japan</option>
                                            <option value="JO">Jordan</option>
                                            <option value="KZ">Kazakhstan</option>
                                            <option value="KE">Kenya</option>
                                            <option value="KI">Kiribati</option>
                                            <option value="KP">Korea, Democratic People's Republic</option>
                                            <option value="KR">Korea, Republic of</option>
                                            <option value="KW">Kuwait</option>
                                            <option value="KG">Kyrgyzstan</option>
                                            <option value="LA">Lao People's Democratic Republic</option>
                                            <option value="LV">Latvia</option>
                                            <option value="LB">Lebanon</option>
                                            <option value="LS">Lesotho</option>
                                            <option value="LR">Liberia</option>
                                            <option value="LY">Libya</option>
                                            <option value="LI">Liechtenstein</option>
                                            <option value="LT">Lithuania</option>
                                            <option value="LU">Luxembourg</option>
                                            <option value="MK">North Macedonia</option>
                                            <option value="MG">Madagascar</option>
                                            <option value="MW">Malawi</option>
                                            <option value="MY">Malaysia</option>
                                            <option value="MV">Maldives</option>
                                            <option value="ML">Mali</option>
                                            <option value="MT">Malta</option>
                                            <option value="MH">Marshall Islands</option>
                                            <option value="MR">Mauritania</option>
                                            <option value="MU">Mauritius</option>
                                            <option value="MX">Mexico</option>
                                            <option value="FM">Micronesia, Federated States of</option>
                                            <option value="MD">Moldova</option>
                                            <option value="MC">Monaco</option>
                                            <option value="MN">Mongolia</option>
                                            <option value="ME">Montenegro</option>
                                            <option value="MA">Morocco</option>
                                            <option value="MZ">Mozambique</option>
                                            <option value="MM">Myanmar</option>
                                            <option value="NA">Namibia</option>
                                            <option value="NR">Nauru</option>
                                            <option value="NP">Nepal</option>
                                            <option value="NL">Netherlands</option>
                                            <option value="NZ">New Zealand</option>
                                            <option value="NI">Nicaragua</option>
                                            <option value="NE">Niger</option>
                                            <option value="NG">Nigeria</option>
                                            <option value="NO">Norway</option>
                                            <option value="OM">Oman</option>
                                            <option value="PK">Pakistan</option>
                                            <option value="PW">Palau</option>
                                            <option value="PA">Panama</option>
                                            <option value="PG">Papua New Guinea</option>
                                            <option value="PY">Paraguay</option>
                                            <option value="PE">Peru</option>
                                            <option value="PH">Philippines</option>
                                            <option value="PL">Poland</option>
                                            <option value="PT">Portugal</option>
                                            <option value="PR">Puerto Rico</option>
                                            <option value="QA">Qatar</option>
                                            <option value="RO">Romania</option>
                                            <option value="RU">Russia</option>
                                            <option value="RW">Rwanda</option>
                                            <option value="KN">Saint Kitts and Nevis</option>
                                            <option value="LC">Saint Lucia</option>
                                            <option value="VC">Saint Vincent and the Grenadines</option>
                                            <option value="WS">Samoa</option>
                                            <option value="SM">San Marino</option>
                                            <option value="ST">Sao Tome and Principe</option>
                                            <option value="SA">Saudi Arabia</option>
                                            <option value="SN">Senegal</option>
                                            <option value="RS">Serbia</option>
                                            <option value="SC">Seychelles</option>
                                            <option value="SL">Sierra Leone</option>
                                            <option value="SG">Singapore</option>
                                            <option value="SK">Slovakia</option>
                                            <option value="SI">Slovenia</option>
                                            <option value="SB">Solomon Islands</option>
                                            <option value="SO">Somalia</option>
                                            <option value="ZA">South Africa</option>
                                            <option value="ES">Spain</option>
                                            <option value="LK">Sri Lanka</option>
                                            <option value="SD">Sudan</option>
                                            <option value="SR">Suriname</option>
                                            <option value="SE">Sweden</option>
                                            <option value="CH">Switzerland</option>
                                            <option value="SY">Syrian Arab Republic</option>
                                            <option value="TW">Taiwan</option>
                                            <option value="TJ">Tajikistan</option>
                                            <option value="TZ">Tanzania</option>
                                            <option value="TH">Thailand</option>
                                            <option value="TL">Timor-Leste</option>
                                            <option value="TG">Togo</option>
                                            <option value="TO">Tonga</option>
                                            <option value="TT">Trinidad and Tobago</option>
                                            <option value="TN">Tunisia</option>
                                            <option value="TR">Turkey</option>
                                            <option value="TM">Turkmenistan</option>
                                            <option value="UG">Uganda</option>
                                            <option value="UA">Ukraine</option>
                                            <option value="AE">United Arab Emirates</option>
                                            <option value="GB">United Kingdom</option>
                                            <option value="US">United States</option>
                                            <option value="UY">Uruguay</option>
                                            <option value="UZ">Uzbekistan</option>
                                            <option value="VU">Vanuatu</option>
                                            <option value="VE">Venezuela</option>
                                            <option value="VN">Vietnam</option>
                                            <option value="YE">Yemen</option>
                                            <option value="ZM">Zambia</option>
                                            <option value="ZW">Zimbabwe</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg col-6">
                                    <div class="form-group">
                                        <div class="label in"><img src="{{route('index')}}/img/marker_in.svg" alt="">
                                            @lang('main.checkin')
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
                                
                                <div class="col-lg col-6">
                                    <div class="form-group">
                                        <button type="submit" class="more"><img src="{{route('index')}}/img/search.svg"
                                                                                alt=""> @lang('main.find')
                                        </button>
                                    </div>
                                </div>
                                
                            </div>
                        </form>

                        <div class="property-list">
                            <div class="property-list-item">
                                <a href="#">
                                    <img src="{{route('index')}}/img/hotel.svg" alt="">
                                    <div class="name">@lang('main.hotels')</div>
                                </a>
                            </div>
                            <div class="property-list-item">
                                <a href="#">
                                    <img src="{{route('index')}}/img/rooms.svg" alt="">
                                    <div class="name">@lang('main.rooms')</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- <div class="places">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>@lang('main.places')</h2>
                    </div>
                </div>
                <div class="row">
                    @foreach($hotels as $hotel)
                        <div class="col-lg-4 col-md-6">
                            <div class="places-item">
                                <a href="{{ route('hotel', $hotel->code) }}">
                                    <span class="img-wrap">
                                        @if($hotel->image)
                                            <img src="{{ Storage::url($hotel->image) }}" alt="">
                                        @else
                                            <img src="{{ route('index')}}/img/noimage.png" alt="">
                                        @endif
                                    </span>
                                </a>
                                <div class="text-wrap">
                                    <div class="address">{{ $hotel->city }}</div>
                                    <h5>{{ $hotel->title }}</h5>
                                    @if($hotel->rating)
                                        <div class="rating"><img src="{{ route('index') }}/img/star.svg"
                                                                 alt=""> {{ $hotel->rating }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-wrap">
                            <a href="{{ route('hotels') }}">@lang('main.more')</a>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <div class="places popular">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>@lang('main.popular')</h2>
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
                            <a href="{{ route('hotels') }}">@lang('main.more')</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <script>
            window.location.href = "{{ route('extranet') }}";
        </script>
    @endauth
@endsection
