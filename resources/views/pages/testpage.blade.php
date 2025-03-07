@extends('layouts.master')

@section('title', 'Главная страница')

@section('content')
    @auth
        <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js"
                integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk="
                crossorigin="anonymous"></script>
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css"
              integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous"/>

        <div class="page">
            <div class="container">
                <div class="row">
                    <div class="col-md-8">
                         @php
                            $hotel = \App\Models\Hotel::where('id', 14)->first();
                            $service = \App\Models\Service::where('hotel_id', 14)->first();
                            $rooms = \App\Models\Room::where('hotel_id', 14)->get();
                         @endphp
                        <h3>{{ $hotel->title }}</h3>
                        <div class="address">Address: {{ $hotel->address }}</div>
                        <div class="amenities">Services: {{ $service->services }}</div>
                        <div class="rooms">
                            <div class="row">
                                @foreach($hotels as $hotel)
                                    <div class="col-md-3">
                                        <div class="room-item">
                                            <h5>{{ $hotel->title }}</h5>
{{--                                            <div class="food">{{ $room->food->title }}</div>--}}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="search homesearch d-xl-block d-lg-block d-none">

            <div class="container">
                <form action="{{ route('searchtest') }}" class="row">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="title">@lang('main.search-title')</label>
                                <select name="title" id="hotel">
                                    <option value="">@lang('main.choose')</option>
                                    @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" data-address="{{ $hotel->__('address')
                                    }}">{{ $hotel->title_en }} ({{ $hotel->title}})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="">@lang('main.search-date')</label>
                                <input type="text" id="date" class="date">
                                <input type="hidden" id="start_d" name="start_d">
                                <input type="hidden" id="end_d" name="end_d">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="">@lang('main.search-adult')</label>
                                <select name="count" id="">
                                    <option value="1" selected>1</option>
                                    <option value="2">2</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group" style="position: relative">
                                <label for="">@lang('main.search-child')</label>
                                <select name="countc" onchange="ageCheck(this);">
                                    <option value="">@lang('main.choose')</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                                <select name="age1" id="age1" class="age">
                                    <option value="0">@lang('main.choose')</option>
                                    <option value="1">1 год</option>
                                    <option value="2">2 года</option>
                                    <option value="3">3 года</option>
                                    <option value="4">4 года</option>
                                    <option value="5">5 лет</option>
                                    <option value="6">6 лет</option>
                                    <option value="7">7 лет</option>
                                    <option value="8">8 лет</option>
                                    <option value="9">9 лет</option>
                                    <option value="10">10 лет</option>
                                    <option value="11">11 лет</option>
                                    <option value="12">12 лет</option>
                                    <option value="13">13 лет</option>
                                    <option value="14">14 лет</option>
                                    <option value="15">15 лет</option>
                                    <option value="16">16 лет</option>
                                    <option value="17">17 лет</option>
                                </select>
                                <select name="age2" id="age2" class="age">
                                    <option value="0">@lang('main.choose')</option>
                                    <option value="1">1 год</option>
                                    <option value="2">2 года</option>
                                    <option value="3">3 года</option>
                                    <option value="4">4 года</option>
                                    <option value="5">5 лет</option>
                                    <option value="6">6 лет</option>
                                    <option value="7">7 лет</option>
                                    <option value="8">8 лет</option>
                                    <option value="9">9 лет</option>
                                    <option value="10">10 лет</option>
                                    <option value="11">11 лет</option>
                                    <option value="12">12 лет</option>
                                    <option value="13">13 лет</option>
                                    <option value="14">14 лет</option>
                                    <option value="15">15 лет</option>
                                    <option value="16">16 лет</option>
                                    <option value="17">17 лет</option>
                                </select>
                                <select name="age3" id="age3" class="age">
                                    <option value="0">@lang('main.choose')</option>
                                    <option value="1">1 год</option>
                                    <option value="2">2 года</option>
                                    <option value="3">3 года</option>
                                    <option value="4">4 года</option>
                                    <option value="5">5 лет</option>
                                    <option value="6">6 лет</option>
                                    <option value="7">7 лет</option>
                                    <option value="8">8 лет</option>
                                    <option value="9">9 лет</option>
                                    <option value="10">10 лет</option>
                                    <option value="11">11 лет</option>
                                    <option value="12">12 лет</option>
                                    <option value="13">13 лет</option>
                                    <option value="14">14 лет</option>
                                    <option value="15">15 лет</option>
                                    <option value="16">16 лет</option>
                                    <option value="17">17 лет</option>
                                </select>
                                <script>
                                    function ageCheck(that) {
                                        if (that.value == 1) {
                                            document.getElementById("age1").style.display = "inline-block";
                                            document.getElementById("age2").style.display = "none";
                                            document.getElementById("age3").style.display = "none";
                                        }
                                        else if (that.value == 2) {
                                            document.getElementById("age1").style.display = "inline-block";
                                            document.getElementById("age2").style.display = "inline-block";
                                            document.getElementById("age3").style.display = "none";
                                        }
                                        else if (that.value == 3) {
                                            document.getElementById("age1").style.display = "inline-block";
                                            document.getElementById("age2").style.display = "inline-block";
                                            document.getElementById("age3").style.display = "inline-block";
                                        }
                                        else {
                                            document.getElementById("age1").style.display = "none";
                                            document.getElementById("age2").style.display = "none";
                                            document.getElementById("age3").style.display = "none";
                                        }
                                    }
                                </script>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <button class="more">@lang('main.search')</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="citizenship">@lang('main.citizenship')</label>
                                <select name="citizenship" id="citizen">
                                    <option value="">@lang('main.choose')</option>
                                    <option value="AX">AALAND ISLANDS</option>
                                    <option value="AF">AFGHANISTAN</option>
                                    <option value="AL">ALBANIA</option>
                                    <option value="DZ">ALGERIA</option>
                                    <option value="AS">AMERICAN SAMOA</option>
                                    <option value="AD">ANDORRA</option>
                                    <option value="AO">ANGOLA</option>
                                    <option value="AI">ANGUILLA</option>
                                    <option value="AQ">ANTARCTICA</option>
                                    <option value="AG">ANTIGUA AND BARBUDA</option>
                                    <option value="AR">ARGENTINA</option>
                                    <option value="AM">ARMENIA</option>
                                    <option value="AW">ARUBA</option>
                                    <option value="AU">AUSTRALIA</option>
                                    <option value="AT">AUSTRIA</option>
                                    <option value="AZ">AZERBAIJAN</option>
                                    <option value="BS">BAHAMAS</option>
                                    <option value="BH">BAHRAIN</option>
                                    <option value="BD">BANGLADESH</option>
                                    <option value="BB">BARBADOS</option>
                                    <option value="BY">BELARUS</option>
                                    <option value="BE">BELGIUM</option>
                                    <option value="BZ">BELIZE</option>
                                    <option value="BJ">BENIN</option>
                                    <option value="BM">BERMUDA</option>
                                    <option value="BT">BHUTAN</option>
                                    <option value="BO">BOLIVIA</option>
                                    <option value="BA">BOSNIA AND HERZEGOWINA</option>
                                    <option value="BW">BOTSWANA</option>
                                    <option value="BV">BOUVET ISLAND</option>
                                    <option value="BR">BRAZIL</option>
                                    <option value="IO">BRITISH INDIAN OCEAN TERRITORY</option>
                                    <option value="BN">BRUNEI DARUSSALAM</option>
                                    <option value="BG">BULGARIA</option>
                                    <option value="BF">BURKINA FASO</option>
                                    <option value="BI">BURUNDI</option>
                                    <option value="KH">CAMBODIA</option>
                                    <option value="CM">CAMEROON</option>
                                    <option value="CA">CANADA</option>
                                    <option value="CV">CAPE VERDE</option>
                                    <option value="KY">CAYMAN ISLANDS</option>
                                    <option value="CF">CENTRAL AFRICAN REPUBLIC</option>
                                    <option value="TD">CHAD</option>
                                    <option value="CL">CHILE</option>
                                    <option value="CN">CHINA</option>
                                    <option value="CX">CHRISTMAS ISLAND</option>
                                    <option value="CO">COLOMBIA</option>
                                    <option value="KM">COMOROS</option>
                                    <option value="CK">COOK ISLANDS</option>
                                    <option value="CR">COSTA RICA</option>
                                    <option value="CI">COTE D'IVOIRE</option>
                                    <option value="CU">CUBA</option>
                                    <option value="CY">CYPRUS</option>
                                    <option value="CZ">CZECH REPUBLIC</option>
                                    <option value="DK">DENMARK</option>
                                    <option value="DJ">DJIBOUTI</option>
                                    <option value="DM">DOMINICA</option>
                                    <option value="DO">DOMINICAN REPUBLIC</option>
                                    <option value="EC">ECUADOR</option>
                                    <option value="EG">EGYPT</option>
                                    <option value="SV">EL SALVADOR</option>
                                    <option value="GQ">EQUATORIAL GUINEA</option>
                                    <option value="ER">ERITREA</option>
                                    <option value="EE">ESTONIA</option>
                                    <option value="ET">ETHIOPIA</option>
                                    <option value="FO">FAROE ISLANDS</option>
                                    <option value="FJ">FIJI</option>
                                    <option value="FI">FINLAND</option>
                                    <option value="FR">FRANCE</option>
                                    <option value="GF">FRENCH GUIANA</option>
                                    <option value="PF">FRENCH POLYNESIA</option>
                                    <option value="TF">FRENCH SOUTHERN TERRITORIES</option>
                                    <option value="GA">GABON</option>
                                    <option value="GM">GAMBIA</option>
                                    <option value="GE">GEORGIA</option>
                                    <option value="DE">GERMANY</option>
                                    <option value="GH">GHANA</option>
                                    <option value="GI">GIBRALTAR</option>
                                    <option value="GR">GREECE</option>
                                    <option value="GL">GREENLAND</option>
                                    <option value="GD">GRENADA</option>
                                    <option value="GP">GUADELOUPE</option>
                                    <option value="GU">GUAM</option>
                                    <option value="GT">GUATEMALA</option>
                                    <option value="GN">GUINEA</option>
                                    <option value="GW">GUINEA-BISSAU</option>
                                    <option value="GY">GUYANA</option>
                                    <option value="HT">HAITI</option>
                                    <option value="HM">HEARD AND MC DONALD ISLANDS</option>
                                    <option value="HN">HONDURAS</option>
                                    <option value="HK">HONG KONG</option>
                                    <option value="HU">HUNGARY</option>
                                    <option value="IS">ICELAND</option>
                                    <option value="IN">INDIA</option>
                                    <option value="ID">INDONESIA</option>
                                    <option value="IQ">IRAQ</option>
                                    <option value="IE">IRELAND</option>
                                    <option value="IL">ISRAEL</option>
                                    <option value="IT">ITALY</option>
                                    <option value="JM">JAMAICA</option>
                                    <option value="JP">JAPAN</option>
                                    <option value="JO">JORDAN</option>
                                    <option value="KZ">KAZAKHSTAN</option>
                                    <option value="KE">KENYA</option>
                                    <option value="KI">KIRIBATI</option>
                                    <option value="KW">KUWAIT</option>
                                    <option value="KG">KYRGYZSTAN</option>
                                    <option value="LA">LAO PEOPLE'S DEMOCRATIC REPUBLIC</option>
                                    <option value="LV">LATVIA</option>
                                    <option value="LB">LEBANON</option>
                                    <option value="LS">LESOTHO</option>
                                    <option value="LR">LIBERIA</option>
                                    <option value="LY">LIBYAN ARAB JAMAHIRIYA</option>
                                    <option value="LI">LIECHTENSTEIN</option>
                                    <option value="LT">LITHUANIA</option>
                                    <option value="LU">LUXEMBOURG</option>
                                    <option value="MO">MACAU</option>
                                    <option value="MG">MADAGASCAR</option>
                                    <option value="MW">MALAWI</option>
                                    <option value="MY">MALAYSIA</option>
                                    <option value="MV">MALDIVES</option>
                                    <option value="ML">MALI</option>
                                    <option value="MT">MALTA</option>
                                    <option value="MH">MARSHALL ISLANDS</option>
                                    <option value="MQ">MARTINIQUE</option>
                                    <option value="MR">MAURITANIA</option>
                                    <option value="MU">MAURITIUS</option>
                                    <option value="YT">MAYOTTE</option>
                                    <option value="MX">MEXICO</option>
                                    <option value="MC">MONACO</option>
                                    <option value="MN">MONGOLIA</option>
                                    <option value="MS">MONTSERRAT</option>
                                    <option value="MA">MOROCCO</option>
                                    <option value="MZ">MOZAMBIQUE</option>
                                    <option value="MM">MYANMAR</option>
                                    <option value="NA">NAMIBIA</option>
                                    <option value="NR">NAURU</option>
                                    <option value="NP">NEPAL</option>
                                    <option value="NL">NETHERLANDS</option>
                                    <option value="AN">NETHERLANDS ANTILLES</option>
                                    <option value="NC">NEW CALEDONIA</option>
                                    <option value="NZ">NEW ZEALAND</option>
                                    <option value="NI">NICARAGUA</option>
                                    <option value="NE">NIGER</option>
                                    <option value="NG">NIGERIA</option>
                                    <option value="NU">NIUE</option>
                                    <option value="NF">NORFOLK ISLAND</option>
                                    <option value="MP">NORTHERN MARIANA ISLANDS</option>
                                    <option value="NO">NORWAY</option>
                                    <option value="OM">OMAN</option>
                                    <option value="PK">PAKISTAN</option>
                                    <option value="PW">PALAU</option>
                                    <option value="PA">PANAMA</option>
                                    <option value="PG">PAPUA NEW GUINEA</option>
                                    <option value="PY">PARAGUAY</option>
                                    <option value="PE">PERU</option>
                                    <option value="PH">PHILIPPINES</option>
                                    <option value="PN">PITCAIRN</option>
                                    <option value="PL">POLAND</option>
                                    <option value="PT">PORTUGAL</option>
                                    <option value="PR">PUERTO RICO</option>
                                    <option value="QA">QATAR</option>
                                    <option value="RE">REUNION</option>
                                    <option value="RO">ROMANIA</option>
                                    <option value="RU">RUSSIAN FEDERATION</option>
                                    <option value="RW">RWANDA</option>
                                    <option value="SH">SAINT HELENA</option>
                                    <option value="KN">SAINT KITTS AND NEVIS</option>
                                    <option value="LC">SAINT LUCIA</option>
                                    <option value="PM">SAINT PIERRE AND MIQUELON</option>
                                    <option value="VC">SAINT VINCENT AND THE GRENADINES</option>
                                    <option value="WS">SAMOA</option>
                                    <option value="SM">SAN MARINO</option>
                                    <option value="ST">SAO TOME AND PRINCIPE</option>
                                    <option value="SA">SAUDI ARABIA</option>
                                    <option value="SN">SENEGAL</option>
                                    <option value="CS">SERBIA AND MONTENEGRO</option>
                                    <option value="SC">SEYCHELLES</option>
                                    <option value="SL">SIERRA LEONE</option>
                                    <option value="SG">SINGAPORE</option>
                                    <option value="SK">SLOVAKIA</option>
                                    <option value="SI">SLOVENIA</option>
                                    <option value="SB">SOLOMON ISLANDS</option>
                                    <option value="SO">SOMALIA</option>
                                    <option value="ZA">SOUTH AFRICA</option>
                                    <option value="GS">SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS</option>
                                    <option value="ES">SPAIN</option>
                                    <option value="LK">SRI LANKA</option>
                                    <option value="SD">SUDAN</option>
                                    <option value="SR">SURINAME</option>
                                    <option value="SJ">SVALBARD AND JAN MAYEN ISLANDS</option>
                                    <option value="SZ">SWAZILAND</option>
                                    <option value="SE">SWEDEN</option>
                                    <option value="CH">SWITZERLAND</option>
                                    <option value="SY">SYRIAN ARAB REPUBLIC</option>
                                    <option value="TW">TAIWAN</option>
                                    <option value="TJ">TAJIKISTAN</option>
                                    <option value="TH">THAILAND</option>
                                    <option value="TL">TIMOR-LESTE</option>
                                    <option value="TG">TOGO</option>
                                    <option value="TK">TOKELAU</option>
                                    <option value="TO">TONGA</option>
                                    <option value="TT">TRINIDAD AND TOBAGO</option>
                                    <option value="TN">TUNISIA</option>
                                    <option value="TR">TURKEY</option>
                                    <option value="TM">TURKMENISTAN</option>
                                    <option value="TC">TURKS AND CAICOS ISLANDS</option>
                                    <option value="TV">TUVALU</option>
                                    <option value="UG">UGANDA</option>
                                    <option value="UA">UKRAINE</option>
                                    <option value="AE">UNITED ARAB EMIRATES</option>
                                    <option value="GB">UNITED KINGDOM</option>
                                    <option value="US">UNITED STATES</option>
                                    <option value="UM">UNITED STATES MINOR OUTLYING ISLANDS</option>
                                    <option value="UY">URUGUAY</option>
                                    <option value="UZ">UZBEKISTAN</option>
                                    <option value="VU">VANUATU</option>
                                    <option value="VE">VENEZUELA</option>
                                    <option value="VN">VIET NAM</option>
                                    <option value="WF">WALLIS AND FUTUNA ISLANDS</option>
                                    <option value="EH">WESTERN SAHARA</option>
                                    <option value="YE">YEMEN</option>
                                    <option value="ZM">ZAMBIA</option>
                                    <option value="ZW">ZIMBABWE</option>
                                </select>
                            </div>
                        </div>
                        <script>
                            $(document).ready(function () {
                                $('#hotel').selectize({
                                    sortField: 'text'
                                });
                                $('#citizen').selectize({
                                    sortField: 'text'
                                });
                                // $('#rating').selectize({
                                //     sortField: 'text'
                                // });
                            });
                        </script>
                        <div class="col">
                            <div class="form-group">
                                <label for="rating">@lang('main.search-rating')</label>
                                <select name="rating" id="rating">
                                    <option value="">@lang('main.choose')</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>

                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="food">@lang('main.search-include')</label>
                                <select id="food_id" name="food_id">
                                    <option value="">@lang('main.choose')</option>
                                    <option value="RO">RO</option>
                                    <option value="BF">BF</option>
                                    <option value="HF">HF</option>
                                    <option value="AI">AI</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="check">@lang('main.search-early')</label>
                                <select name="early_in" id="early_in">
                                    <option value="">@lang('main.choose')</option>
                                    <option value="06:00">06:00</option>
                                    <option value="07:00">07:00</option>
                                    <option value="08:00">08:00</option>
                                    <option value="09:00">09:00</option>
                                    <option value="10:00">10:00</option>
                                    <option value="11:00">11:00</option>
                                    <option value="12:00">12:00</option>
                                    <option value="13:00">13:00</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="check">@lang('main.search-late')</label>
                                <select name="early_out" id="early_out">
                                    <option value="">@lang('main.choose')</option>
                                    <option value="15:00">15:00</option>
                                    <option value="16:00">16:00</option>
                                    <option value="17:00">17:00</option>
                                    <option value="18:00">18:00</option>
                                    <option value="19:00">19:00</option>
                                    <option value="20:00">20:00</option>
                                    <option value="21:00">21:00</option>
                                    <option value="22:00">22:00</option>
                                    <option value="23:00">23:00</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group check">
                                <input type="checkbox" id="cancelled" name="cancelled">
                                <label for="cancelled">@lang('main.cancelled')</label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group check">
                                <input type="checkbox" name="extra_place" id="extra_place">
                                <label for="extra_place">@lang('main.search-extra')</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <style>
            .check {
                margin-top: 40px;
            }
            input[type="checkbox"] {
                width: auto;
                height: auto;
                display: inline-block;
            }
            .homesearch form button.more:hover {
                background-color: #035497;
                color: #fff;
            }
            select.age{
                position: relative;
                left: 0;
                bottom: -5px;
                height: 30px;
                width: 32%;
                padding: 0;
                display: none;
            }
            .selectize-input {
                height: 50px;
                padding: 5px 15px;
            }
            .selectize-input > input {
                height: 50px;
                top: -5px;
                position: relative;
            }
            .selectize-input > * {
                position: relative;
                top: 10px;
            }
        </style>
    @else
        <div class="page">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                            <div class="descr">Необходимо пройти <a href="{{ route('login') }}">авторизацию</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endauth

@endsection
