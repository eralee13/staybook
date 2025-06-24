@extends('auth.layouts.hotelhead')

@section('title', 'Консоль')

@section('content')

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <canvas id="bookingChart" height="100"></canvas>

                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        const ctx = document.getElementById('bookingChart').getContext('2d');
                        const chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: @json($dates),
                                datasets: [{
                                    label: 'Бронирования по датам',
                                    data: @json($counts),
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    fill: true,
                                    tension: 0.3
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });
                    </script>

                    <canvas id="roomRateChart" height="100"></canvas>
                    <script>
                        const ctx2 = document.getElementById('roomRateChart').getContext('2d');
                        const chart2 = new Chart(ctx2, {
                            type: 'bar',
                            data: {
                                labels: ['Номера', 'Тарифы'],
                                datasets: [{
                                    label: 'Количество',
                                    data: [{{ $roomCount }}, {{ $rateCount }}],
                                    backgroundColor: ['rgba(54, 162, 235, 0.6)', 'rgba(255, 206, 86, 0.6)'],
                                    borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)'],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>

@endsection