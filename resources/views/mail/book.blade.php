<style>
    body{
        margin:0; padding:20px; font-family: Arial, sans-serif; background-color: #b9b9b9 !important;
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
    $book = \App\Models\Book::where('id', $book->id)->first();
    $room = \App\Models\Room::where('id', $book->room_id)->first();
    $rate = \App\Models\Rate::where('id', $book->rate_id)->first();

        $freeCancelDate = '';
        if( isset($rate->cancellation_rule_id) ){
            
            $cancel = \App\Models\CancellationRule::where('id', $rate->cancellation_rule_id)->first();

            if( isset($cancel->end_date) ){
                $freeCancelDate = \Carbon\Carbon::parse($cancel->end_date)->format('d F Y, H:i');
            } 
        }
        

    $meal = optional(optional($rate)->meal_id ? \App\Models\Meal::find($rate->meal_id) : null)->title ?? 'Без питания';
    \Carbon\Carbon::setLocale( app()->getLocale() );
    $arrivalDate = \Carbon\Carbon::parse($book->arrivalDate)->format('d F Y');
    $departureDate = \Carbon\Carbon::parse($book->departureDate)->format('d F Y');
    $createdDate = \Carbon\Carbon::parse($book->created_at)->format('d F Y, H:i');
    $today = \Carbon\Carbon::now()->format('d F Y');
    $time = \Carbon\Carbon::now()->format('H:i');
@endphp

  <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 650px; margin: auto; background-color: #fff; border-collapse: collapse; box-shadow: 0 0 10px rgba(0,0,0,0.05);">

    <!-- Header block -->
    <tr>
      <td style="background-color: #4a4a4a; color: #fff; padding: 20px;">
        <div class="logo" style="width:200px !important; height: 60px !important; display: inline-block;">
                        <img src="{{ route('index')  }}/img/logo.svg" alt="Logo" style="width:
                        140px !important; height: 45px !important">
                    </div>
        <div class="phone"><a href="tel:+996 227 225 227">+996 227 225 227</a></div>
        <h2 style="margin: 0; font-size: 18px;">{{ $hotel->title_en ?? $hotel->title ?? 'Название отеля не указан'}}</h2>
            <p style="margin: 5px 0 0; font-size: 13px;">
                {{ $region->title ?? '' }}, 
                {{ $hotel->city ?? '' }}, 
                {{ $hotel->address_en ?? $hotel->address ?? '' }}</p>
        </td>
    </tr>

    <!-- Guest & Dates -->
    <tr>
      <td style="padding: 20px; background-color: #f8f8f8;">
        <table width="100%" style="font-size: 14px;">
          <tr>
            <td><strong>Guest</strong><br>{{ $book->title ?? '' }}</td>
            <td align="right"><strong>Meal</strong><br>{{ $meal ?? '' }}</td>
          </tr>
          <tr>
            <td style="padding-top: 15px;"><strong>Check-in</strong><br>{{ $arrivalDate }}, {{ $hotel->checkin ?? ''}}</td>
            <td style="padding-top: 15px;" align="right"><strong>Check-out</strong><br>{{ $departureDate }}, {{ $hotel->checkout ?? ''}}</td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- Room title -->
    <tr>
      <td style="padding: 15px 20px;">
        <h3 style="margin: 0; font-size: 15px; color: #333;">{{ $room->title_en ?? $room->title ?? ''}}</h3>
        <p style="margin: 5px 0 0; font-size: 13px; color: #666;">Room {{ $book->room_count ?? ''}}. For {{ $book->adult + $book->child ?? ''}} quests</p>
      </td>
    </tr>

    <!-- Divider -->
    <tr>
      <td><hr style="border: none; border-top: 1px solid #ddd; margin: 0;"></td>
    </tr>

    <!-- Booking details -->
    <tr>
      <td style="padding: 20px;">
        <table width="100%" style="font-size: 14px;" cellpadding="6">
            <tr>
                <td><strong>Booking №</strong></td>
                <td align="right">{{ $book->id ?? ''}}</td>
            </tr>
            <tr>
                <td><strong>Booking made on</strong></td>
                <td align="right">{{ $createdDate }}</td>
            </tr>
            {{-- <tr>
                <td><strong>Payment type</strong></td>
                <td align="right">Paid</td>
            </tr> --}}
            <tr>
                <td><strong>Rate</strong></td>
                <td align="right">{{ $rate->title_en ?? $rate->title ?? ''}}</td>
            </tr>
            <tr>
                <td><strong>Beddings</strong></td>
                <td align="right">{{ $rate->bed_type ?? '' }}</td>
            </tr>
            <tr>
                <td><strong>Free cancellation</strong></td>
                <td align="right">until {{ $freeCancelDate }}</td>
            </tr>
            <tr>
                <td><strong>Cancellation charge:</strong></td>
                <td align="right">{{ $book->cancel_penalty ?? 0 }} {{ $book->currency}}</td>
            </tr>
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

