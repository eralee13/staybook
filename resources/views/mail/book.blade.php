<div class="page">
    <h3>Бронь создана</h3>
    <table>
        <tr>
            <td>
                <div class="logo"><img src="{{ route('index')  }}/img/logo.svg" alt="Logo" style="width:
                    100px;"></div>
            </td>
            <td>
                <div class="phone"><a href="tel:+996 227 225 227">+996 227 225 227</a></div>
            </td>
        </tr>
        <tr>
            <td>ID</td>
            <td>{{ $book->id }}</td>
        </tr>
        <tr>
            <td>
                @php
                    $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->orWhere('exely_id', $book->hotel_id)->firstOrFail();
                @endphp
                {{ $hotel->title }}<br>
            </td>
            <td>{{ $hotel->address }}</td>
        </tr>
        <tr>
            <td>Guest</td>
            <td>
                {{ $book->title }}<br>
                {{ $book->adult }} adult<br>
                {{ $book->phone }}
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
            <td>{{ $book->arrivalDate }} from {{ $hotel->checkin }}</td>
        </tr>
        <tr>
            <td>Check Out</td>
            <td>{{ $book->departureDate }} until {{ $hotel->checkout }}</td>
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
            <td>Accommodation price</td>
            <td>{{ $book->sum }}</td>
        </tr>
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
