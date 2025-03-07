@extends('auth.layouts.master')

@isset($book)
    @section('title', 'Редактировать бронирование ' . $book->title)
@else
    @section('title', 'Добавить бронирование')
@endisset

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @isset($book)
                        <h1>Редактировать бронирование {{ $book->title }}</h1>
                    @else
                        <h1>Добавить бронирование</h1>
                    @endisset
                    <form method="post"
                          @isset($book)
                              action="{{ route('books.update', $book) }}"
                          @else
                              action="{{ route('books.store') }}"
                            @endisset
                    >
                        @isset($book)
                            @method('PUT')
                        @endisset
                        <div class="form-group">
                            <label for="">Отель</label>
                            <select name="hotels" id="hotels">
                                <option>Все отели</option>
                                @foreach($hotels as $hotel)
                                    <option value="{{ $hotel->id }}" @selected(old('hotels') == $hotel->id)
                                    >{{ $hotel->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Выберите комнату</label>
                            <select name="rooms" id="rooms">
                            </select>
                        </div>
{{--                            <div id="price" class="hidden">{{ $room->price }}</div>--}}
{{--                            <div id="pricec" class="hidden">{{ $room->pricec }}</div>--}}

                        <input type="hidden" name="status" id="status" value="Забронирован">
                        <div class="form-group">
                            <label class="col-xs-4" for="title">Ваше имя</label>
                            <input type="text" class="form-control" name="title" id="title" required/>
                            <span id="titleError" class="text-danger"></span>
                        </div>

                        <div class="form-group">
                            <label class="col-xs-4" for="phone">Номер телефона</label>
                            <input type="number" class="form-control" name="phone" id="phone" required>
                            <div id="output" class="output"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-4" for="email">Email</label>
                            <input type="email" class="form-control" name="email" id="email"/>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-4" for="content">Комментарий</label>
                            <input type="text" class="form-control" name="comment" id="comment"/>
                        </div>

                        <div class="form-group">
                            <label class="col-xs-4" for="count">Кол-во взрослых</label>
                            <select name="count" id="count" required>
                                <option value="0">Выбрать</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="col-xs-4" for="countс">Кол-во детей</label>
                            <select name="countc" id="countc" required>
                                <option value="0">Выбрать</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Стоимость</label>
                            <input type="text" id="sum" name="sum" readonly>
                        </div>

                        <div class="form-group">
                            <label class="col-xs-4" for="start_d">Дата заезда:</label>
                            <input type="text" name="start_d" readonly id="start_d"/>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-4" for="end_d">Дата выезда</label>
                            <input type="text" name="end_d" readonly id="end_d"/>
                        </div>
                        @csrf
                        <button class="more">Отправить</button>
                        <a href="{{url()->previous()}}" class="btn delete cancel">Отмена</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function () {
        $('#hotels').on('change', function () {
            let idHotel = this.value;
            $("#rooms").html('');
            $.ajax({
                url: "{{url('api/fetch-rooms')}}",
                type: "POST",
                data: {
                    hotel_id: idHotel,
                    _token: '{{csrf_token()}}'
                },
                dataType: 'json',
                success: function (result) {
                    $('#rooms').html('<option value="">Выбрать</option>');
                    $.each(result.rooms, function (key, value) {
                        $("#rooms").append('<option value="' + value
                            .id + '" data-price="' + value.price + '" data-pricec="' + value.pricec + '" ' +
                            'data-count="' + value.count + '">' + value
                            .title + '</option>');
                    });
                    //$("#rooms").append('<div class="price">' + value.price + '"</div>"');
                }
            });
        });

        $("#count, #countc").change(function(){
            let price = $('#price').text();
            let pricec = $('#pricec').text();
            let count = $('#count').val();
            //let countc = $('#countc').val();
            let start_d = $.fullCalendar.formatDate(start, "Y-MM-DD");
            let end_d = $.fullCalendar.formatDate(end, "Y-MM-DD");
            let st = new Date(start_d);
            let en = new Date(end_d);
            let millisecondsPerDay = 1000 * 60 * 60 * 24;
            let millisBetween = en.getTime() - st.getTime();
            let days = millisBetween / millisecondsPerDay;
            let sum = (price * count * days) + (pricec * countc * days);
            $('#sum').val(sum + ' сом');
        });

    });
</script>