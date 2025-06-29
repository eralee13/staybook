@extends('layouts.filter_mini')

@section('title', 'Об отеле')

@section('content')

    @if(app()->getLocale() == 'ru')
        <div class="page about">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>@lang('main.about_service')</h1>
                        <h4>StayBook – часть Silk Way Group</h4>
                    </div>
                </div>
                <div class="row aic">
                    <div class="col-md-10 order-xl-1 order-lg-1 order-2">
                        <p>Silk Way Group, рег. № 162129-3301-OOO</p>
                        <p>Юридический адрес: 1V ул. Ашар, 720077, г. Бишкек, Кыргызская Республика</p>
                        <p>Фактический адрес: проспект Чингиза Айтматова, 91, 720044, Бишкек, Кыргызская Республика</p>
                        <p>StayBook является зарегистрированным сервисным знаком в Кыргызской Республике.</p>
                    </div>
                    <div class="col-md-2 order-xl-2 order-lg-2 order-1">
                        <img src="{{ route('index') }}/img/about_logo.svg" alt="">
                    </div>
                </div>
                <div class="row aic">
                    <div class="col-md-2">
                        <img src="{{ route('index') }}/img/about_person.svg" alt="">
                    </div>
                    <div class="col-md-10">
                        <p><b>StayBook</b> – ваш надежный B2B-партнер по онлайн-бронированию <br> отелей в Центральной
                            Азии и на Кавказе</p>
                    </div>
                </div>
                <div class="row aic">
                    <div class="col-md-10 order-xl-1 order-lg-1 order-2">
                        <p><b>StayBook</b> – это инновационная B2B-платформа для туристических профессионалов,
                            обеспечивающая удобное и выгодное бронирование отелей в Кыргызстане, Казахстане,
                            Узбекистане, Туркменистане, Таджикистане, Азербайджане, Армении и Грузии.

                        </p>
                    </div>
                    <div class="col-md-2 order-xl-2 order-lg-2 order-1">
                        <img src="{{ route('index') }}/img/about_prop.png" alt="">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h5>Почему выбирают StayBook?
                        </h5>
                        <ul>
                            <li>Прямые контракты с отелями – эксклюзивные тарифы и гибкие условия сотрудничества.</li>
                            <li>Конкурентные цены – специальные предложения и лучшие тарифы для наших партнеров.</li>
                            <li>Круглосуточная техническая поддержка – оперативная и надежная помощь в любое время.</li>
                            <li>Удобный интерфейс – быстрый и легкий доступ к лучшим отелям региона.</li>
                            <li>Надежный B2B-инструмент – прозрачное сотрудничество и гибкие варианты оплаты.</li>
                        </ul>
                    </div>
                </div>
                <div class="row aic">
                    <div class="col-md-2">
                        <img src="{{ route('index') }}/img/about_hand.png" alt="">
                    </div>
                    <div class="col-md-10">
                        <p>StayBook гарантирует высокое качество сервиса, конкурентные цены и надежное партнерство в
                            сфере гостиничного бизнеса!</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <p><b><span>Присоединяйтесь к StayBook уже сегодня и расширяйте свои бизнес-возможности в Центральной Азии и на Кавказе!

                        </span></b></p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="page about">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>@lang('main.about_service')</h1>
                        <h4>StayBook – part of Silk Way Group</h4>
                    </div>
                </div>
                <div class="row aic">
                    <div class="col-md-10 order-xl-1 order-lg-1 order-2">
                        <p>Silk Way Group, № 162129-3301-OOO</p>
                        <p>Legal address: 1V Ashar St., 720077, Bishkek, Kyrgyz Republic</p>
                        <p>Actual address: 91 Chingiz Aitmatov Avenue, 720044, Bishkek, Kyrgyz Republic</p>
                        <p>StayBook is a registered service mark in the Kyrgyz Republic.</p>
                    </div>
                    <div class="col-md-2 order-xl-2 order-lg-2 order-1">
                        <img src="{{ route('index') }}/img/about_logo.svg" alt="">
                    </div>
                </div>
                <div class="row aic">
                    <div class="col-md-2">
                        <img src="{{ route('index') }}/img/about_person.svg" alt="">
                    </div>
                    <div class="col-md-10">
                        <p><b>StayBook</b> – your reliable B2B partner for online hotel booking <br>
                            in Central Asia and the Caucasus.</p>
                    </div>
                </div>
                <div class="row aic">
                    <div class="col-md-10 order-xl-1 order-lg-1 order-2">
                        <p><b>StayBook</b> – is an innovative B2B platform for travel professionals, providing
                            convenient and profitable hotel booking in Kyrgyzstan, Kazakhstan, Uzbekistan, Turkmenistan,
                            Tajikistan, Azerbaijan, Armenia, and Georgia.</p>
                    </div>
                    <div class="col-md-2 order-xl-2 order-lg-2 order-1">
                        <img src="{{ route('index') }}/img/about_prop.png" alt="">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h5>Why choose StayBook?</h5>
                        <ul>
                            <li>Direct contracts with hotels – exclusive rates and flexible cooperation terms.</li>
                            <li>Competitive prices – special offers and the best rates for our partners.</li>
                            <li>24/7 technical support – prompt and reliable assistance anytime.</li>
                            <li>User-friendly interface – quick and easy access to the best hotels in the region.</li>
                            <li>Reliable B2B tool – transparent collaboration and flexible payment options.</li>
                        </ul>
                    </div>
                </div>
                <div class="row aic">
                    <div class="col-md-2">
                        <img src="{{ route('index') }}/img/about_hand.png" alt="">
                    </div>
                    <div class="col-md-10">
                        <p>StayBook guarantees high-quality service, competitive prices, and reliable partnership in the
                            hotel industry!</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <p><b><span>Join StayBook today and expand your business opportunities in Central Asia and the Caucasus!
					</span></b></p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
