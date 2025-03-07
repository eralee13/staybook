<div class="page">
    <table>
        <tr>
            <td>
                <div class="logo"><img src={{ route('index')  }}/img/logo.svg" alt="Logo" style="width:
                    100px;"></div>
            </td>
            <td>
                <div class="phone"><a href="tel:+996 772 511 511">+996 772 511 511</a></div>
            </td>
        </tr>
        <tr>
            <td>ID</td>
            <td>{{ $book->id }}</td>
        </tr>
        <tr>
            <td>
                @php
                    $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->firstOrFail();
                @endphp
                {{ $hotel->title }}<br>
            </td>
            <td>{{ $hotel->address }}</td>
        </tr>
        <tr>
            <td>Guest</td>
            <td>
                {{ $book->title }}<br>
                @isset($book->title2)
                    {{ $book->title2 }}<br>
                @endisset
                @isset($book->titlec1)
                    {{ $book->titlec1 }} - ({{$book->age1}})<br>
                @endisset
                @isset($book->titlec2)
                    {{ $book->titlec2 }} - ({{$book->age2}})<br>
                @endisset
                @isset($book->titlec3)
                    {{ $book->titlec3 }} - ({{$book->age3}})
                @endisset
            </td>
        </tr>
{{--        <tr>--}}
{{--            <td>Meal</td>--}}
{{--            <td>--}}
{{--                @php--}}
{{--                    $room = \App\Models\Room::where('id', $book->room_id)->firstOrFail();--}}
{{--                @endphp--}}
{{--                {{ $room->include }}--}}
{{--            </td>--}}
{{--        </tr>--}}
        <tr>
            <td>Check In</td>
            <td>{{ $book->start_d }} from {{ $hotel->checkin }}</td>
        </tr>
        <tr>
            <td>Check Out</td>
            <td>{{ $book->end_d }} until {{ $hotel->checkout }}</td>
        </tr>
        <tr>
            @php
                $room = \App\Models\Room::where('id', $book->room_id)->first();
             @endphp
            <td>
                <img src="{{ Storage::url($room->image) }}"
                     alt="Logo" style="width: 200px;">
            </td>
            <td>{{ $room->title }}</td>
        </tr>

        <tr>
            <td>Booking made on</td>
            <td>{{ date('Y-m-d H:i:s') }}</td>
        </tr>
        <tr>
            <td>Payment type</td>
            <td>Paid</td>
        </tr>
        <tr>
            <td>Rate</td>
            <td>B2B</td>
        </tr>
        <tr>
            <td>Bedding</td>
            <td>{{ $room->bed }}</td>
        </tr>
{{--        <tr>--}}
{{--            <td>Free cancellation</td>--}}
{{--            <td>--}}
{{--                @if($room->hotel->cancelled == 0 || $room->hotel->cancelled == '')--}}
{{--                    @isset($book->end_d)--}}
{{--                        @php--}}
{{--                            $date = \Carbon\Carbon::parse($book->end_d);--}}
{{--                            //$date->locale('ru');--}}
{{--                            $exp = $date->subDays($room->cancel_day);--}}
{{--                            $month = $exp->getTranslatedMonthName('Do MMMM');--}}
{{--                            $get = $exp->day . ' ' . $month;--}}
{{--                        @endphp--}}
{{--                    @endisset--}}
{{--                @endif--}}
{{--                until {{ $get }}--}}
{{--            </td>--}}
{{--        </tr>--}}
{{--        <tr>--}}
{{--            <td>Meal price</td>--}}
{{--            <td>Included</td>--}}
{{--        </tr>--}}
        <tr>
            <td>Accommodation price</td>
            <td>{{ $book->sum }}</td>
        </tr>
{{--        <tr>--}}
{{--            <td>Price per day</td>--}}
{{--            <td>{{ $room->price }} $</td>--}}
{{--        </tr>--}}
    </table>

    <style>
        .page {
            padding: 40px;
            background-color: #333;
            color: #fff;
        }

        table {
            width: 100%;
        }

        table td {
            padding: 10px;
            border-top: 1px solid #ddd;
        }
    </style>
</div>
