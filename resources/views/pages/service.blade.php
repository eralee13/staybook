@extends('layouts.master')

@section('title', 'О сервисе')

@section('content')

    @if(app()->getLocale() == 'ru')
        <div class="page-about">
            <div class="page about">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h1>StayBook – часть Silk Way Group</h1>
                            <p>Silk Way Group, рег. № 162129-3301-OOO</p>
                            <p>Юридический адрес: 1V ул. Ашар, 720077, г. Бишкек, Кыргызская Республика</p>
                            <p>Фактический адрес: проспект Чингиза Айтматова, 91, 720044, Бишкек, Кыргызская
                                Республика</p>
                            <p>StayBook является зарегистрированным сервисным знаком в Кыргызской Республике.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="partner">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-md-12">
                            <p>StayBook – ваш надежный B2B-партнер по онлайн-бронированию отелей в Центральной Азии
                                и на Кавказе</p>
                            <p>StayBook – это инновационная B2B-платформа для туристических профессионалов,
                                обеспечивающая удобное и выгодное бронирование отелей в Кыргызстане, Казахстане,
                                Узбекистане,
                                Туркменистане, Таджикистане, Азербайджане, Армении и Грузии.</p>
                        </div>
                        <div class="col-lg-4">
                            <img src="img/partner.png" alt="">
                        </div>
                    </div>
                </div>
            </div>

            <div class="why">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="text-wrap">
                                <h2>Почему выбирают StayBook?</h2>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">01</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Прямые контракты с отелями</h5>
                                    <p>эксклюзивные тарифы и гибкие условия сотрудничества</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">02</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Конкурентные цены </h5>
                                    <p>специальные предложения и лучшие тарифы для наших партнеров</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">03</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Круглосуточная техническая поддержка </h5>
                                    <p>оперативная и надежная помощь в любое время</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">04</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Удобный интерфейс</h5>
                                    <p>быстрый и легкий доступ к лучшим отелям региона</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">05</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Надежный B2B-инструмент</h5>
                                    <p>прозрачное сотрудничество и гибкие варианты оплаты</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="garant">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="wrap">
                                <p>StayBook гарантирует высокое качество сервиса, конкурентные цены и надежное
                                    партнерство в
                                    сфере
                                    гостиничного бизнеса!</p>
                                <p>Присоединяйтесь к StayBook уже сегодня и расширяйте свои бизнес-возможности в
                                    Центральной Азии и на Кавказе!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="page-about">
            <div class="page about">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h1>StayBook – part of Silk Way Group</h1>
                            <p>Silk Way Group, Reg. No. 162129-3301-OOO</p>
                            <p>Registered address: 1V Ashar Street, 720077, Bishkek, Kyrgyz Republic</p>
                            <p>Actual address: 91 Chingiz Aitmatov Avenue, 720044, Bishkek, Kyrgyz Republic</p>
                            <p>StayBook is a registered service mark in the Kyrgyz Republic.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="partner">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 col-md-12">
                            <p>StayBook – your trusted B2B partner for seamless online hotel bookings across Central
                                Asia and the Caucasus.</p>
                            <p>StayBook is an innovative B2B platform designed for travel professionals, offering fast,
                                convenient, and cost-effective access to hotels in Kyrgyzstan, Kazakhstan, Uzbekistan,
                                Turkmenistan, Tajikistan, Azerbaijan, Armenia, and Georgia.</p>
                        </div>
                        <div class="col-lg-4">
                            <img src="img/partner.png" alt="">
                        </div>
                    </div>
                </div>
            </div>

            <div class="why">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="text-wrap">
                                <h2>Why choose StayBook?</h2>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">01</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Direct contracts with hotels</h5>
                                    <p>Exclusive rates and flexible partnership terms</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">02</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Competitive prices</h5>
                                    <p>Special offers and the best rates for our partners</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">03</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>24/7 technical support</h5>
                                    <p>Reliable and prompt assistance anytime</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">04</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>User-friendly interface</h5>
                                    <p>Fast and easy access to the best hotels in the region</p>

                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">05</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Reliable B2B tool</h5>
                                    <p>Transparent cooperation and flexible payment options</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="garant">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="wrap">
                                <p>StayBook guarantees high-quality service, competitive prices, and reliable
                                    partnership in the hospitality industry!</p>
                                <p>Join StayBook today and expand your business opportunities across Central Asia and
                                    the Caucasus!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
