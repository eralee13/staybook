<h1 data-aos="fade-up" data-aos-duration="2000">{{ $message }}</h1>

@php
    $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->first();
    $arrival = \Carbon\Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
    $departure = \Carbon\Carbon::createFromDate($book->departureDate)->format('d.m.Y');
    // $cancel = \App\Models\CancellationRule::where('id', $book->cancellation_id)->firstOrFail();
    $cancel_date = \Carbon\Carbon::createFromDate($book->created_at)->format('d.m.Y H:i');
    $room = \App\Models\Room::where('id', $book->room_id)->firstOrFail();
    $rate = \App\Models\Rate::where('id', $book->rate_id)->firstOrFail();
@endphp

<div class="alert alert-danger">Статус: {{ $status ?? $book->status }}</div>

<ul>
    <li>Номер брони: {{ $book->id }}</li>
    <li>
        @if( isset($cancelRules->refundable) == true)
            Бесплатная отмена действует до {{ $cancelRule->end_date }} (UTC {{ $hotel->utc ?? ''}}). 
                Иначе размер штрафа: {{ $book->cancel_penalty }} {{ $book->currency ?? 'CNY' }}
        @else
            Возможность бесплатной отмены отсутствует. Размер штрафа: {{ $book->cancel_penalty }} {{ $book->currency ?? 'CNY' }}
        @endif
    <li>Отель: {{ $hotel->title }}</li>
    <li>Дата заеда/выезда: {{ $arrival }} {{ $hotel->checkin }} - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $hotel->utc }})</li>
    <li>Тип комнаты: {{ $room->title ?? ''}}</li>
    <li>Тариф: {{ $rate->title ?? ''}}</li>
    <li>Кол-во гостей: {{ $book->adult ?? ''}}</li>
</ul>