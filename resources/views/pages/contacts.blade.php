@extends('layouts.master')

@section('title', 'Контакты')

@section('content')

    <div class="page-about contacts">
        <div class="page about">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>@lang('main.contact_us')</h1>
                        @if(app()->getLocale() == 'ru')
                        <p><a href="mailto:{{ $contacts->email }}">{{ $contacts->email }}</a> по любым другим
                            вопросам</p>
                        @else
                            <p>For any other inquiries: <a href="mailto:{{ $contacts->email }}">{{ $contacts->email }}</a></p>
                        @endif

                        <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
                        <div id="map" style="width: 100%; height: 450px;"></div>
                        <script>
                            DG.then(function () {
                                var map = DG.map('map', {
                                    center: [42.839085, 74.584437],
                                    zoom: 16
                                });

                                DG.marker([42.839085, 74.584437], { scrollWheelZoom: false })
                                    .addTo(map)
                                    .bindLabel('StayBook', {
                                        static: true
                                    });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>

        <div class="form">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="wrap">
                            <form action="{{ route('contact_mail') }}" method="post">
                                <h3>@lang('main.contact_form')</h3>
                                @if(app()->getLocale() == 'ru')
                                    <p>Заполните все поля и нажмите «Отправить», мы обработаем ваше обращение и свяжемся в
                                    течение 24 часов.</p>
                                @else
                                    <p>Fill in all fields and click “Submit”. We will process your request and get back to you within 24 hours.</p>
                                @endif
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="text" placeholder="@lang('main.name')" name="name">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="email" placeholder="Email" name="email">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="text" id="phone" name="phone">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <input type="text" name="message" placeholder="@lang('main.message')">
                                    </div>
                                    <div class="form-group check">
                                        <input type="checkbox" id="check">
                                        @if(app()->getLocale() == 'ru')
                                        <label for="check">Согласен с <a href="{{ route('privacy') }}">условиями соглашения</a></label>
                                        @else
                                            <label for="check">I agree to the <a href="{{ route('privacy') }}">terms and conditions</a>
                                            </label>
                                        @endif
                                    </div>
                                    {!! NoCaptcha::display() !!}
                                    @csrf
                                    <div class="btn-wrap">
                                        <button class="more">@lang('main.send')</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
