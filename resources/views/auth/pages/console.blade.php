@extends('auth.layouts.master')

@section('title', 'Консоль')

@section('content')

    <style>
        .page-item{
            background-color: #fff;
            border-radius: 30px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>


    <div class="page">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="page-item">
                                <h6>Кол-во отелей</h6>
                                {{ \App\Models\Hotel::count() }}
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="page-item">
                                <h6>Кол-во комнат</h6>
                                {{ \App\Models\Page::count() }}
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="page-item">
                                <h6>Кол-во бронирований</h6>
                                {{ \App\Models\Book::count() }}
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="page-item">
                                <h6>Кол-во страниц</h6>
                                {{ \App\Models\Page::count() }}
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="page-item">
                                <h6>Кол-во пользователей</h6>
                                {{ \App\Models\User::count() }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h2>График бронирований</h2>

                            <canvas id="bookingsChart" style="max-width: 700px"></canvas>

                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const ctx = document.getElementById('bookingsChart');

                                    new Chart(ctx, {
                                        type: 'line',
                                        data: {
                                            labels: @json($labels),
                                            datasets: [{
                                                label: 'Бронирования',
                                                data: @json($data),
                                                borderWidth: 2,
                                            }]
                                        }
                                    });
                                });
                            </script>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection