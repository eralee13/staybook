@if($message == 'Бронирование успешно создано!' || $message == 'Этот бронь уже существует!')
    <h1>Поздравляем!</h1>

        <div class="alert alert-primary" role="alert">
            <strong>{{ $message }}</strong>
        </div>
@else
    <h1>Ошибка бронирования</h1>

        <div class="alert alert-danger" role="alert">
            <strong>{{ $message }}</strong>
        </div>
@endif
    
    <ul>
        <li>Статус: {{ $book->status ?? '' }}</li>
        <li>Номер брони: {{ $book->id ?? ''}}</li>
        <li>ID отеля: {{ $request->hotel_id ?? ''}}</li>
        <li>
            Даты: {{ Carbon\Carbon::createFromDate($request->arrivalDate)->format('d.m.Y') }} {{$hotel->checkin ?? ''}} 
            - {{ Carbon\Carbon::createFromDate($request->departureDate)->format('d.m.Y') }} {{ $hotel->checkout ?? ''}}
            (UTC {{ $request->utc }})
        </li>
        <li>
            @if($request->refundable == true)
                
                    Бесплатная отмена действует до {{ \Carbon\Carbon::parse($request->cancelDate)->format('d.m.Y') }} 
                    (UTC {{ $request->utc }})!
                    
                    Иначе размер штрафа: {{ $request->cancelPrice }} {{ $request->currency ?? '$' }}
            @else
                    Невозвратный тариф.
            @endif
        <li>
            Заказчик: {{ $request->name ? $book->title : '' }}
            <ul>
                <li>Номер
                    телефона: {{ $request->phone ?? '' }}</li>
                <li>
                    Email: {{ $request->email ?? '' }}</li>
                <li>Комментарий: {{ $request->comment ?? '' }}</li>
            </ul>
        </li>
    </ul>
    @if( isset($book->id) ) 
        <div class="bnt-wrap">
            <form action="{{ route('cancel_calculate', $book->id) }}">
                <input type="hidden" name="number" value="{{ $book->book_token }}">
                <button class="more">Отменить бронь</button>
            </form>
        </div>
    @endif
