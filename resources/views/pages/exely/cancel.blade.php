<h1 data-aos="fade-up" data-aos-duration="2000">Ваша бронь отменена</h1>
<div class="alert alert-danger">Статус: {{ $book->status }}</div>

@php
    $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->first();
    $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
    $arrival = \Carbon\Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
    $departure = \Carbon\Carbon::createFromDate($book->departureDate)->format('d.m.Y');
    $cancel = \App\Models\CancellationRule::where('id', $book->cancellation_id)->firstOrFail();
    $cancel_date = \Carbon\Carbon::createFromDate($book->created_at)->format('d.m.Y H:i');
    $room = \App\Models\Room::where('id', $book->room_id)->firstOrFail();
    $rate = \App\Models\Rate::where('id', $book->rate_id)->firstOrFail();
@endphp

<ul>
    <li>Номер брони: {{ $book->book_token }}</li>
    {{--                            <li>Дата отмены: {{ $cancel_date }}</li>--}}
    <li>
        @if($cancel->is_refundable == true)
            <td>Бесплатная отмена действует до {{ $cancelDate }} (UTC {{ $hotel_utc }}). Размер
                штрафа: {{ $book->cancel_penalty }} {{ $order->booking->currencyCode ?? '$' }}</td>
        @else
            <td>Возможность бесплатной отмены отсутствует. Размер штрафа: {{ $book->cancel_penalty }} {{ $order->booking->currencyCode ?? '$' }}</td>
    @endif
    <li>Отель: {{ $hotel->title }}</li>
    <li>Дата заеда/выезда: {{ $arrival }} {{ $hotel->checkin }} - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $hotel_utc }})</li>
    <li>Тип комнаты: {{ $room->roomType->name ?? $room->title }}</li>
    <li>Тариф: {{ $room->ratePlan->name ?? $rate->title }}</li>
    <li>Кол-во гостей: {{ $room->guestCount->adultCount ?? $book->adult }}</li>
</ul>