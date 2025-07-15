@extends('layouts.head_extranet')

@section('title', 'Extranet')

@section('content')

    @if(app()->getLocale() == 'ru')
        <div class="welcome">
            <div class="main">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 order-xl-1 order-lg-1 order-md-1 order-2">
                            <h1>Добро пожаловать в Экстранет StayBook</h1>
                            <p>Цифровое решение для отельеров и партнёров по размещению в Центральной Азии.
                            </p>
                            <p>Мы — StayBook, часть Silk Way Group, и мы создаём продукт с душой здесь, в Кыргызстане.
                            </p>
                            <p>Наша цель — сделать туризм умным, прозрачным и доступным. Мы верим, что технологии должны
                                помогать бизнесу расти, а не усложнять его. Именно поэтому мы создали Экстранет StayBook
                                —
                                удобную платформу, через которую вы можете управлять всем: от загрузки тарифов до
                                отслеживания
                                бронирований и платежей.</p>
                            <div class="btn-wrap">
                                <a href="{{ route('login') }}" class="more">Авторизоваться</a>
                            </div>
                        </div>
                        <div class="col-md-6 order-xl-2 order-lg-2 order-md-2 order-1">
                            <img src="{{ route('index') }}/img/about_logo.svg" alt="">
                        </div>
                    </div>
                </div>
            </div>

            <div class="why">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h2>Почему стоит работать с нами:</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="why-item">
                                <img src="{{ route('index') }}/img/why1.png" alt="">
                                <p>Всё под рукой — управление ценами, номерами, доступностью и бронированиями</p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="why-item">
                                <img src="{{ route('index') }}/img/why2.png" alt="">
                                <p>Продвижение вашего объекта среди B2B-партнёров по всему СНГ, Кавказу и Азии</p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="why-item">
                                <img src="{{ route('index') }}/img/why3.png" alt="">
                                <p>Автоматизация процессов — экономим ваше время и энергию</p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="why-item">
                                <img src="{{ route('index') }}/img/why4.png" alt="">
                                <p>Персональный подход — вы не просто номер в системе</p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="why-item">
                                <img src="{{ route('index') }}/img/why5.png" alt="">
                                <p>Техподдержка, которая действительно помогает — на русском, английском и
                                    кыргызском</p>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="why-item">
                                <img src="{{ route('index') }}/img/why6.png" alt="">
                                <p>Централизованные выплаты, закрывающие документы и удобная отчётность</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="who">
                <div class="container">
                    <div class="row">
                        <div class="col-md-5">
                            <img src="{{ route('index') }}/img/about_logo.svg" alt="">
                            <h2>Кто мы:</h2>
                            <h5>StayBook — это больше, чем просто OTA. Это часть большой экосистемы Silk Way
                                Group, которая объединяет:</h5>
                        </div>
                        <div class="col-md-7">
                            <div class="who-item">
                                <div class="descr">
                                    <b>Silk Way Travel (Outbound)</b> — выездной туризм и корпоративные поездки
                                </div>
                            </div>
                            <div class="who-item">
                                <div class="descr"><b>Silk Way Travel (Inbound)</b> — приём туристов в Кыргызстан и
                                    Центральную Азию
                                </div>
                            </div>
                            <div class="who-item">
                                <div class="descr"><b>Free Way</b> — внутренний и молодежный туризм</div>
                            </div>
                            <div class="who-item">
                                <div class="descr"><b>Silk Way Logistics</b> — трансферы, деловые поездки, встречи в
                                    аэропорту
                                </div>
                            </div>
                            <div class="who-item">
                                <div class="descr"><b>StayBook OTA</b> — технологичная B2B платформа для бронирований по
                                    всему региону
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="losung">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-3 col-md-5">
                            <img src="{{ route('index') }}/img/map2.png" alt="">
                        </div>
                        <div class="col-lg-9 col-md-7">
                            <p>Мы родом из Кыргызстана, и мы верим в силу локального продукта.</p>
                            <p>Наша миссия — оцифровать туристический рынок региона и дать каждому партнёру — от
                                маленькой юрты
                                до пятизвёздочного отеля — равные возможности роста.</p>
                            <div class="btn-wrap">
                                <a href="{{ route('login') }}" class="more">Авторизоваться</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="welcome">
            <div class="main">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 order-xl-1 order-lg-1 order-md-1 order-2">
                            <h1>Welcome to the StayBook Extranet</h1>
                            <p>A digital solution for hoteliers and property partners across Central Asia.
                            </p>
                            <p>We are StayBook - part of the Silk Way Group </p>
                            <p>StayBook Extranet — a simple platform where you can manage everything: from rates and
                                availability to bookings and payments.</p>
                            <div class="btn-wrap">
                                <a href="{{ route('login') }}" class="more">Login</a>
                            </div>
                        </div>
                        <div class="col-md-6 order-xl-2 order-lg-2 order-md-2 order-1">
                            <img src="{{ route('index') }}/img/about_logo.svg" alt="">
                        </div>
                    </div>
                </div>
            </div>

            <div class="why">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h2>Why work with us:</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="why-item">
                                <img src="{{ route('index') }}/img/why1.png" alt="">
                                <p>Full control of rates, availability, and reservations</p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="why-item">
                                <img src="{{ route('index') }}/img/why2.png" alt="">
                                <p>Direct access to our wide B2B network across the CIS, Caucasus, and Asia</p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="why-item">
                                <img src="{{ route('index') }}/img/why3.png" alt="">
                                <p>Automation that saves time and boosts revenue</p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="why-item">
                                <img src="{{ route('index') }}/img/why4.png" alt="">
                                <p>A personal approach — you’re not just a number in the system</p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="why-item">
                                <img src="{{ route('index') }}/img/why5.png" alt="">
                                <p>Support that really supports — in English, Russian, and Kyrgyz</p>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="why-item">
                                <img src="{{ route('index') }}/img/why6.png" alt="">
                                <p>Centralized payments, official invoicing, and transparent reporting</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="who">
                <div class="container">
                    <div class="row">
                        <div class="col-md-5">
                            <img src="{{ route('index') }}/img/about_logo.svg" alt="">
                            <h2>Who we are:</h2>
                            <h5>StayBook is more than just an OTA — it’s part of the larger Silk Way Group ecosystem,
                                which unites:</h5>
                        </div>
                        <div class="col-md-7">
                            <div class="who-item">
                                <div class="descr">
                                    <b>Silk Way Travel (Outbound)</b> — outbound travel and corporate services
                                </div>
                            </div>
                            <div class="who-item">
                                <div class="descr"><b>Silk Way Travel (Inbound)</b> — inbound tourism to Kyrgyzstan and
                                    Central Asia
                                </div>
                            </div>
                            <div class="who-item">
                                <div class="descr"><b>Free Way</b> — youth and domestic tourism</div>
                            </div>
                            <div class="who-item">
                                <div class="descr"><b>Silk Way Logistics</b> — transfers, MICE, and airport meet & greet
                                </div>
                            </div>
                            <div class="who-item">
                                <div class="descr"><b>StayBook OTA</b> — a smart B2B platform for hotel bookings across
                                    the region
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="losung">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-3 col-md-5">
                            <img src="{{ route('index') }}/img/map2.png" alt="">
                        </div>
                        <div class="col-lg-9 col-md-7">
                            <p>We’re proud to be a homegrown tech company from Kyrgyzstan, working to digitize the
                                tourism industry and give every partner — from a cozy guesthouse to a luxury hotel — an
                                equal opportunity to grow.</p>
                            <div class="btn-wrap">
                                <a href="{{ route('login') }}" class="more">Login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
