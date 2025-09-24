@extends('layouts.master')

@section('title', 'О сервисе')

@section('content')

    @if(app()->getLocale() == 'ru')
        <div class="page-about company">
            <div class="page about">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h1>StayBook — это часть группы компаний Silk Way Group</h1>
                            <p>Мы специализируемся на онлайн-бронировании отелей, апартаментов и других объектов размещения
                                в странах СНГ, Центральной Азии и Кавказа. Наша цель — сделать размещение удобным,
                                технологичным и выгодным для всех: отелей, компаний и туристических агентств.</p>
                            <p>Silk Way Group — одна из самых динамично развивающихся туристических групп в регионе, с
                                сильным фокусом на качество сервиса, инновации и развитие международного партнёрства.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="why">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="text-wrap">
                                <h2>В состав Silk Way Group входят</h2>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">01</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Silk Way Travel</h5>
                                    <p>выездной и индивидуальный туризм</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">02</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Silk Way Visa</h5>
                                    <p>визовая поддержка по всему миру</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">03</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Silk Way Inbound</h5>
                                    <p>и по Центральной Азии</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">04</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Silk Way Business</h5>
                                    <p>деловые поездки и организация мероприятий (MICE)</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">05</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Silk Way Logistics</h5>
                                    <p>трансферы, логистика и сопровождение гостей</p>
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
                                <p>StayBook — технологичная B2B-OTA
                                    платформа бронирования <br>
                                    Мы работаем на стыке технологий, сервиса
                                    и настоящего человеческого подхода, создавая решения, которые помогают бизнесу расти,
                                    а путешествиям становиться проще и доступнее.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="callback">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="wrap">
                                <h3>Хотите сотрудничать?</h3>
                                <p>Пишите на <a href="mailto:contract@silkwaytravel.kg">contract@silkwaytravel.kg</a> или
                                    звоните: <a href="tel:+996 227 225 227">+996 227 225 227</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="page-about company">
            <div class="page about">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h1>StayBook is part of Silk Way Group.</h1>
                            <p>We specialize in online bookings of hotels, apartments, and other accommodation across the CIS countries, Central Asia, and the Caucasus. Our goal is to make accommodation convenient, innovative, and beneficial for everyone: hotels, companies, and travel agencies.</p>
                            <p>Silk Way Group is one of the fastest-growing travel groups in the region, with a strong focus on service quality, innovation, and the development of international partnerships.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="why">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="text-wrap">
                                <h2>Silk Way Group includes</h2>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">01</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Silk Way Travel</h5>
                                    <p>outbound and tailor-made travel</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">02</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Silk Way Visa</h5>
                                    <p>global visa support</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">03</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Silk Way Inbound</h5>
                                    <p>across Central Asia</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">04</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Silk Way Business</h5>
                                    <p>деловые поездки и организация мероприятий (MICE)</p>
                                </div>
                            </div>
                            <div class="row why-item">
                                <div class="col-md-2 col-2">
                                    <div class="num">05</div>
                                </div>
                                <div class="col-md-10 col-10">
                                    <h5>Silk Way Logistics</h5>
                                    <p>transfers, logistics, and guest services</p>
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
                                <p>StayBook is a cutting-edge B2B OTA booking platform.<br>
                                    We work at the intersection of technology, service, and a truly human approach, creating solutions that help businesses grow and make travel simpler and more accessible.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="callback">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="wrap">
                                <h3>Interested in partnership?</h3>
                                <p>Email us at <a href="mailto:contract@silkwaytravel.kg">{{ $contacts->email }}</a> or call: <a href="tel:{{ $contacts->phone }}">{{ $contacts->phone }}</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
