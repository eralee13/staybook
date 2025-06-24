@php
    if ( isset($tmroom->Hotels[0]->RoomTypes) ){
        // Сортируем номера и тарифы по минимальной цене
        $sortedRoom = collect($tmroom->Hotels[0]->RoomTypes)
        // Сначала сортируем тарифы внутри каждого номера
        ->map(function ($room) {
            $room->RateInfos = collect($room->RateInfos)
                ->sortBy('TotalPrice')
                ->values();
            return $room;
        })
        // Потом сортируем сами номера по минимальной цене из RateInfos
        ->sortBy(function ($room) {
            return $room->RateInfos[0]->TotalPrice ?? PHP_INT_MAX;
        })
        ->values();
    } else {
        $sortedRoom = [];
    }
@endphp

@foreach($sortedRoom as $tmroom)
    <div class="row" style="margin-top: 30px">
        <div class="col-md-3">
            <div class="room">
                @if ( isset($tmimages[$loop->index]) )
                    <img src="{{ Storage::url($tmimages[$loop->index]->image) }}" alt="">
                @else
                    <img src="{{ route('index') }}/img/noimage.png" alt=""
                            width="100px">
                @endif
                <h5>{{ $tmroom->Name }}</h5>
                {{--   <div class="bed">2 отдельные кровати</div> --}}
                <div class="amenities">
                    @if( isset($roomAmenity) )
                        @foreach($roomAmenity as $amenity)
                            @php
                                $iconFile = 'check.svg';
                                foreach ($iconMap as $keyword => $filename) {
                                    if (mb_stripos($amenity, $keyword) !== false) {
                                        $iconFile = $filename;
                                        break;
                                    }
                                }
                            @endphp
                                <div class="amenities-item">
                                    <img src="{{ asset('img/icons/' . $iconFile) }}" alt="{{ $amenity }}">
                                    <div class="name">{{ $amenity }}</div>
                                </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="tariff-wrap">
                <div class="owl-carousel owl-tariffs">
                    @include('pages.search.tourmind.rates', ['tmrates' => $tmroom->RateInfos, 'tmimage' => $tmimages[$loop->index]->image ?? ''])
                </div>
            </div>
        </div>

    </div>
@endforeach