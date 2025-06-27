<!DOCTYPE  html>
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
      <title>voucher-en-260553810.pdf</title>
      <style type="text/css"> * {margin:0; padding:0; text-indent:0; }
         .s1 { color: black; font-family:"DejaVu Sans", sans-serif; font-style: normal; font-weight: normal; text-decoration: none; font-size: 12pt; }
         h2 { color: black; font-family:"DejaVu Sans", sans-serif; font-style: normal; font-weight: bold; text-decoration: none; font-size: 8pt; }
         .s2 { color: black; font-family:"DejaVu Sans", sans-serif; font-style: normal; font-weight: normal; text-decoration: none; font-size: 7pt; }
         p { color: black; font-family:"DejaVu Sans", sans-serif; font-style: normal; font-weight: normal; text-decoration: none; font-size: 6.5pt; margin:0pt; }
         .s3 { color: black; font-family:"DejaVu Sans", sans-serif; font-style: normal; font-weight: bold; text-decoration: none; font-size: 9pt; }
         .s4 { color: black; font-family:"DejaVu Sans", sans-serif; font-style: normal; font-weight: normal; text-decoration: none; font-size: 9pt; }
         h1 { color: black; font-family:"DejaVu Sans", sans-serif; font-style: normal; font-weight: bold; text-decoration: none; font-size: 10.5pt; }
         .s5 { color: black; font-family:"DejaVu Sans", sans-serif; font-style: normal; font-weight: normal; text-decoration: none; font-size: 8pt; }
         table, tbody {vertical-align: top; overflow: visible; }
         p{ line-height: 1.2; padding-left: 5pt; margin-bottom: 5px;}
         h1, h2{ padding-left: 5pt; margin-bottom: 5px;}
      </style>
   </head>
   <body style="padding: 50px;">

    @php
        $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->first();
        $contacts = \App\Models\Contact::first();
        $room = \App\Models\Room::where('id', $book->room_id)->first();
        $img = \App\Models\Image::where('hotel_id', $book->hotel_id)->first();
        $rate = \App\Models\Rate::where('id', $book->rate_id)->first();
        $meal = \App\Models\Meal::where('id', $rate->meal_id)->first('title');
        $region = \App\Models\City::where('name', $hotel->city)->first('title');
        $arrivalDate = \Carbon\Carbon::parse($book->arrivalDate)->format('d.m.Y');
        $departureDate = \Carbon\Carbon::parse($book->departureDate)->format('d.m.Y');
        $today = date('d.m.Y');
    @endphp

        <div style="float: left; width: 50%; padding-left: 5pt;">
            <img width="50" src=".\img\logo-100.jpg" />
        </div>
        <div style="float: right; width: 50%; ">
            <h2 style="text-indent: 0pt;text-align: right;">Reservation 260553810 made on {{ $today }} </h2>
            <p class="s2" style="padding-top: 2pt;text-indent: 0pt;text-align: right;">This accommodation is booked by our partner</p>
        </div>

        <div style="clear: both; padding-top: 10pt;">
            <h2 style="text-indent: 0pt;text-align: left;">Silk Way Travel</h2>
            <p style="text-indent: 0pt;text-align: left;">+996772511511</p>
        </div>
      
      <p style="text-indent: 0pt;text-align: left;"><br/></p>
      <table style="border-collapse:collapse;margin-left:6.7179pt; width: 100%;" cellspacing="0">
         <tr style="height:51pt">
            <td style="width:332pt; border-top-style:solid;border-top-width:2pt;border-top-color:#c7e1f6;border-left-style:solid;border-left-width:2pt;border-left-color:#c7e1f6;border-bottom-style:solid;border-bottom-width:2pt;border-bottom-color:#c7e1f6;border-right-style:solid;border-right-width:2pt;border-right-color:#c7e1f6" rowspan="2">
                
                    <p style="clear: both; text-indent: 0pt;text-align: left; float: left; padding-top: 5pt;  margin-right: 10pt;">
                        @if( isset($img->image) )
                            <img style="max-width: 82pt;" src="{{ public_path('storage/' . $img->image) }}"/>
                        @endif
                    </p>
                   
                    <p style=" float: left;">
                        <p class="s3" style="padding-top: 2pt;  text-align: left;">{{ $hotel->title ?? $hotel->title_en ?? ''}}</p>
                        <p class="s4" style="text-indent: 0pt; text-align: left; margin-bottom: 2pt;">{{ $region->title}}, {{ $hotel->city}}, {{ $hotel->address ?? $hotel->address_en  ?? ''}}</p>
                        <p class="s4" style=" text-indent: 0pt; text-align: left;">{{ $hotel->phone ?? ''}} </p>
                    </p>

                
            </td>
            <td style="width:142pt; border-top-style:solid;border-top-width:2pt;border-top-color:#c7e1f6;border-left-style:solid;border-left-width:2pt;border-left-color:#c7e1f6;border-bottom-style:solid;border-bottom-width:1pt;border-bottom-color:#c7e1f6;border-right-style:solid;border-right-width:2pt;border-right-color:#c7e1f6">
               <div>
                <p class="s3" style="padding-top: 7pt;padding-left: 8pt; text-indent: 0pt;text-align: left;">Check-in <br> {{ $arrivalDate }}, from. <br>
                    {{ $hotel->checkin ?? ''}}
               </p>
               
               </div>
            </td>
         </tr>
         <tr style="height:51pt">
            <td style="width:142pt;border-top-style:solid;border-top-width:1pt;border-top-color:#c7e1f6;border-left-style:solid;border-left-width:2pt;border-left-color:#c7e1f6;border-bottom-style:solid;border-bottom-width:2pt;border-bottom-color:#c7e1f6;border-right-style:solid;border-right-width:2pt;border-right-color:#c7e1f6">
               <div>
                <p class="s3" style="padding: 6pt; text-indent: 0pt;text-align: left;">Check-out: <br> {{ $departureDate }}, until <br>  
                    {{ $hotel->checkout ?? ''}} 
               </p>
               
               </div>
            </td>
         </tr>
      </table>
      <p style="text-indent: 0pt;text-align: left;"><br/></p>
      <h1 style="text-indent: 0pt;text-align: left;">{{ $room->title ?? $room->title_en ?? ''}}, for {{ $book->adult ?? ''}} adults</h1>
      <h2 style="padding-top: 6pt; text-indent: 0pt;text-align: left;">Bedding:      
        <span class="s5">{{ $rate->bed_type ?? ''}}</span></h2>

      <p style="text-indent: 0pt; text-align: left;">
      <h2 style="padding-bottom: 6pt; text-indent: 0pt;text-align: left;">Guests:       
        <span class="s5" style="text-transform: uppercase;">{{ $book->title ?? $book->title_en ?? ''}}</span></h2>

        <hr style="border: none; border-top: 2px dotted #c7e1f6;  margin-top: 10px; margin-bottom: 10px; margin-left: 5pt;">

      <div style="width: 48%; float: left;  line-height: 1.5;">
        <h2>Important. Please Note</h2>
        <p>Hotels may charge additional mandatory fees payable by the guest
        directly at the property, including but not exclusively: resort fee, facility
        fee, city tax, fee for the stay of foreign citizens.
        </p>
        <p>
            The guest can be also asked to provide a credit card or cash deposit as
        a guarantee of payment for additional services such as: mini-bar, payTV, etc.
        </p>
        <p>
             Agency is not responsible for the quality of services provided by the
            hotel. Client can contact administration of hotel directly to claim on
            volume and quality of provided services. In case of any issues during
            check-in or check-out, please contact the Agency.
        </p>
        <h2>Amendment & Cancellation Policy</h2>
        <p>
            An alteration of Reservation by the Customer is considered as a
        cancellation of Reservation and making new Reservation. We'll try to
        negotiate the order amendment with the supplier but we cannot
        guarantee it will be approved.
        </p>
        <p>
            Cancellation of reservation or no-show may result in penalties,
        according to rate and contract terms.
        </p>
        <p>
            Please notify in advance if you expect to check-in after 6 pm. Hotel may
        cancel the reservation and charge the no-show fee in case you don’t
        show up by that time.
        </p>
      </div>
      <div style="width: 52%; float: left; padding-left: 15pt; line-height: 1.5;">
        <h2>Meal type</h2>
        <p>
            {{ $meal->title ?? '' }}
        </p>
      </div>
      <div style="clear: both;  width: 100%; padding-top: 20px; padding-left: 5pt; float: none;">
            @php
                $lat = old('lat', isset($hotel->lat) ? $hotel->lat : 42.8746);
                $lng = old('lng', isset($hotel->lng) ? $hotel->lng : 74.6120);
                $zoom = 15;
                $width = 500;
                $height = 180;

                // Генерируем URL статического изображения карты
                $mapUrl = "https://static-maps.yandex.ru/1.x/?ll=$lng,$lat&size={$width},{$height}&z=$zoom&l=map&pt=$lng,$lat,pm2rdl";

            @endphp

            @if($lat && $lng)
                <img src="{{ $mapUrl }}" alt="Карта" style="width:100%; height: 240px; max-width:680px;">
            @else
                <p>Координаты карты не указаны</p>
            @endif
      </div>
   </body>
</html>