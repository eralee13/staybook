
@foreach($etgroom['data']['hotels'] as $room)
    <div class="row" style="margin-top: 30px">
        <div class="col-md-3">
            <div class="room">
                <div class="img-wrap">
                    @if ( isset($tmimages[$loop->index]) )
                        <img src="{{ Storage::url($tmimages[$loop->index]->image) }}" alt="">
                    @else
                        <img src="{{ route('index') }}/img/noimage.png" alt=""
                             width="100px">
                    @endif
                </div>
                {{-- <h5>{{ $room['room_name'] }}</h5> --}}
                
               <div class="text-wrap">
                   <div class="amenities">
                       @if( !empty($roomAmenity[0]) )
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
        </div>
        <div class="col-md-9">
            <div class="tariff-wrap">
                <div class="owl-carousel owl-tariffs">
                    @include('pages.search.emerging.rates', ['rates' => $room['rates'], 'tmimage' => $tmimages[$loop->index]->image ?? ''])
                </div>
            </div>
        </div>
    </div>
@endforeach