<style>
    body {
            font-family: Arial, sans-serif; 
            background-color: #b9b9b9 !important; 
            margin: 0; padding: 20px;
        }
    .phone{
        display: inline-block;
        vertical-align: middle;
        height: 60px;
        float: right;
        padding-right: 20px;
        padding-top: 10px;
    }
</style>
@php
    $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->orWhere('exely_id', $book->hotel_id)->first();
    $region = \App\Models\City::where('name', $hotel->city)->first('title');
    // $book = \App\Models\Book::where('id', $book->id)->first();
    $room = \App\Models\Room::where('id', $book->room_id)->first();
    $rate = \App\Models\Rate::where('id', $book->rate_id)->first();

        $freeCancelDate = '';
        if( isset($rate->cancellation_rule_id) ){
            
            $cancel = \App\Models\CancellationRule::where('id', $rate->cancellation_rule_id)->first();

            if( isset($cancel->end_date) ){
                $freeCancelDate = \Carbon\Carbon::parse($cancel->end_date)->format('d F Y, H:i');
            } 
        }
    

    $meal = optional(optional($rate)->meal_id ? \App\Models\Meal::find($rate->meal_id) : null)->title ?? 'No meal';
    \Carbon\Carbon::setLocale( app()->getLocale() );
    $arrivalDate = \Carbon\Carbon::parse($book->arrivalDate)->format('d F Y');
    $departureDate = \Carbon\Carbon::parse($book->departureDate)->format('d F Y');
    $today = \Carbon\Carbon::now()->format('d F Y');
    $time = \Carbon\Carbon::now()->format('H:i');
    $createdDate = \Carbon\Carbon::parse($book->created_at)->format('d F Y, H:i');
    $cancelDate = \Carbon\Carbon::parse($book->cancel_date)->format('d F Y, H:i');
    
@endphp
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 40px auto; background-color: #fff; border-radius: 6px; overflow: hidden;">
        <tr>
            <td style="background-color: #333; color: #fff; padding: 20px;">
                <div class="logo" style="width:200px !important; height: 60px !important; display: inline-block;">
                        <img src="{{ route('index')  }}/img/logo.svg" alt="Logo" style="width:
                        140px !important; height: 45px !important">
                    </div>
                <div class="phone"><a href="tel:+996 227 225 227">+996 227 225 227</a></div>
                <h2 style="margin: 0;">{{ $hotel->title_en ?? $hotel->title ?? 'Название отеля не указан'}}</h2>
                <p style="margin: 5px 0 0;">{{ $region->title ?? '' }}, {{ $hotel->city ?? '' }}, {{ $hotel->address_en ?? $hote->address ?? ''}}</p>
            </td>
        </tr>

        <tr>
            <td style="padding: 20px;">
                <table width="100%" style="margin-bottom: 15px;">
                    <tr>
                        <td><strong>Quests:</strong> {{ $book->title ?? '' }}</td>
                        <td><strong>Meal:</strong> {{ $meal ?? '' }}</td>
                    </tr>
                    <tr>
                        <td><strong>CheckIn:</strong> {{ $arrivalDate }}, {{ $hotel->checkin ?? '00:00'}}</td>
                        <td><strong>CheckOut:</strong> {{ $departureDate }}, {{ $hotel->checkout ?? '00:00'}}</td>
                    </tr>
                </table>

                <hr style="border: none; border-top: 1px solid #ccc;">

                <h3 style="color: #ad0606; margin: 10px 0;">{{ $room->title_en ?? $room->title ?? ''}}</h3>
                <p style="margin: 0;">Room {{ $book->room_count ?? ''}}. for {{ $book->adult + $book->child ?? ''}} quests</p>

                <hr style="border: none; border-top: 1px solid #ccc; margin: 20px 0;">

                <table width="100%" cellpadding="5">
                    <tr>
                        <td><strong>Booking №:</strong></td>
                        <td align="right">{{ $book->id ?? ''}}</td>
                    </tr>
                    <tr>
                        <td><strong>Cancel date:</strong></td>
                        <td align="right">{{ $cancelDate ?? '' }}</td>
                    </tr>
                    {{-- <tr>
                        <td><strong>Тип оплаты:</strong></td>
                        <td align="right">Оплачено</td>
                    </tr> --}}
                    <tr>
                        <td><strong>Rate:</strong></td>
                        <td align="right">{{ $rate->title_en ?? $rate->title ?? ''}}</td>
                    </tr>
                    <tr>
                        <td><strong>Beddings</strong></td>
                        <td align="right">{{ $rate->bed_type ?? '' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Free cancellation:</strong></td>
                        <td align="right">until {{ $freeCancelDate }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cancellation charge:</strong></td>
                        <td align="right">{{ $book->cancel_penalty ?? 0 }} {{ $book->currency}}</td>
                    </tr>
                    {{-- <tr>
                        <td><strong>Стоимость питания:</strong></td>
                        <td align="right">{{ $meal ?? $meal->code}}</td>
                    </tr> --}}
                    <tr>
                        <td><strong>Accommodation cost:</strong></td>
                        <td align="right" style="color: #000; font-size: 16px;"><strong>{{ $book->sum }} {{ $book->currency}}</strong></td>
                    </tr>
                </table>

                <div style="text-align: center; margin-top: 25px;">
                    <a href="{{ route('index') }}/auth/listbooks/show/{{$book->id}}" style="display: inline-block; background-color: #0061ae; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 4px; font-weight: bold;">Manage booking</a>
                </div>
            </td>
        </tr>

        <tr>
            <td style="background-color: #0061ae; color: #fff; text-align: center; padding: 15px;">
                {{-- <p style="margin: 0;">Нужна помощь? <strong>Отдел поддержки отелей</strong></p> --}} <br>
            </td>
        </tr>
    </table>


