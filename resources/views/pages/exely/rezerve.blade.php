<h1>Поздравляем!</h1>
        <ul>
            <li>Статус: {{ $res->booking->status ?? $book->status }}</li>
            <li>Номер брони: {{ $res->booking->number ?? $book->id }}</li>
            <li>ID отеля: {{ $res->booking->propertyId ?? $book->hotel_id }}</li>
            @php
                $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->first();
                $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                $arrival = \Carbon\Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
                $departure = \Carbon\Carbon::createFromDate($book->departureDate)->format('d.m.Y');
                $cancel = \App\Models\CancellationRule::where('id', $book->cancellation_id)->firstOrFail();
                $cancelDate = \Carbon\Carbon::parse($arrival)->subDays($cancel->free_cancellation_days)->format('d.m.Y H:i');
            @endphp

            <li>Даты: {{ $arrival }} {{$hotel->checkin}} - {{ $departure }} {{ $hotel->checkout }}
                (UTC {{ $hotel_utc }})
            </li>
            <li>
                @if($cancel->is_refundable == true)
                    <td>
                        @if(now() <= $cancelDate)
                            Бесплатная отмена действует до {{ $cancelDate }} (UTC {{ $hotel_utc }}).
                        @endif
                        Размер
                        штрафа: {{ $book->cancel_penalty }} {{ $order->booking->currencyCode ?? '$' }}</td>
                @else
                    <td>Возможность бесплатной отмены отсутствует. Размер
                        штрафа: {{ $book->cancel_penalty }} {{ $order->booking->currencyCode ?? '$' }}</td>
            @endif
            <li>
                Заказчик: {{ $book->title }}
                <ul>
                    <li>Номер
                        телефона: {{ $res->booking->customer->contacts->phones[0]->phoneNumber ?? $book->phone }}</li>
                    <li>
                        Email: {{ $res->booking->customer->contacts->emails[0]->emailAddress ?? $book->email }}</li>
                    <li>Комментарий: {{ $res->booking->customer->comment ?? $book->email }}</li>
                </ul>
            </li>
        </ul>
        <div class="bnt-wrap">
            <form action="{{ route('cancel_calculate', $book->id) }}">
                <input type="hidden" name="number" value="{{ $res->booking->number ?? $book->book_token }}">
                <input type="hidden" name="currency" value="{{ $res->booking->currencyCode ?? '$' }}">
                <input type="hidden" name="cancelTime"
                        value="{{ $res->booking->cancellationPolicy->freeCancellationDeadlineUtc ?? $cancelDate }}">
                <button class="more">Отменить бронь</button>
            </form>
        </div>