@php
    use App\Models\Image;$room      = $item['roomStay'] ?? null;
    $hotel     = $item['hotel'] ?? null;
    $title     = is_object($hotel) ? $hotel->title : ($hotel['title'] ?? '');
    $images    = $hotel?->id ? Image::where('hotel_id', $hotel->id)->get() : collect();
    $amenities = $hotel && $hotel->amenity ? explode(',', $hotel->amenity->services) : [];
    $itemsAmen = array_slice($amenities, 0, 8);
    $converted = $item['conv_total'] ?? 0;
    $symbol    = $item['conv_symbol'] ?? '';
    $amount = is_numeric($converted) ? (float)$converted : 0.0;
    $dataId    = $hotel?->id ?? ($room->propertyId ?? null);
@endphp

<div class="search-item ex" data-type="{{ $hotel->type ?? '' }}" data-id="{{ $hotel->id }}">
    <div class="row">
        <div class="col-md-6 order-1">
            <div class="img-wrap">
                <div class="owl-carousel owl-slider">
                    <div class="slider-item">
                        @if($hotel?->image)
                            <img src="{{ Storage::url($hotel->image) }}" alt="">
                        @elseif($images->isNotEmpty())
                            <img src="{{ Storage::url($images->first()->image) }}" alt="">
                            @foreach($images->slice(1, 2) as $file)
                                <div class="primary"><img src="{{ Storage::url($file->image) }}" alt=""></div>
                            @endforeach
                        @else
                            <img src="{{ route('index')}}/img/noimage.png" alt="">
                        @endif
                    </div>
                </div>
                <div class="text-wrap">
                    <div class="price">
                        @lang('main.from') {{ number_format((float)($item['conv_total'] ?? 0), 0, '.', ' ') }} {{ $item['conv_symbol'] ?? '' }}
                    </div>
                    <div class="night">@lang('main.night')</div>
                    @if($hotel?->rating)
                        <div class="rating"><img src="{{ route('index') }}/img/star.svg" alt=""> {{ $hotel->rating }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6 order-2">
            <div class="wrap">
                <h4>{{ $hotel?->title }}</h4>
                <div class="amenities">
                    @foreach($itemsAmen as $amenity)
                        <div class="amenities-item">
                            <img src="{{ asset('img/icons/check.svg') }}" alt="{{ $amenity }}">
                            <div class="name">{{ $amenity }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="address">{{ $hotel?->__('address') }}</div>
                <div class="btn-wrap">
                    <form action="{{ route('findHotelExely', $room->roomType->id) }}">
                        <input type="hidden" name="propertyId" value="{{ $room->propertyId }}">
                        <input type="hidden" name="arrivalDate" value="{{ request('arrivalDate') }}">
                        <input type="hidden" name="departureDate" value="{{ request('departureDate') }}">
                        <input type="hidden" name="adultCount" value="{{ $room->guestCount->adultCount }}">
                        @php $array_child = []; @endphp
                        @foreach(($room->guestCount->childAges ?? []) as $child)
                            @php $array_child[] = $child; @endphp
                        @endforeach
                        <input type="hidden" name="childAges[]" value="{{ implode(', ', $array_child) }}">
                        <input type="hidden" name="ratePlanId" value="{{ $room->ratePlan->id }}">
                        <input type="hidden" name="roomTypeId" value="{{ $room->roomType->id }}">
                        @foreach(($room->roomType->placements ?? []) as $type)
                            <input type="hidden" name="roomType" value="{{ $type->kind }}">
                            <input type="hidden" name="roomCount" value="{{ $type->count }}">
                            <input type="hidden" name="roomCode" value="{{ $type->code }}">
                            <input type="hidden" name="minAge" value="{{ $type->minAge }}">
                            <input type="hidden" name="maxAge" value="{{ $type->maxAge }}">
                        @endforeach
                        <input type="hidden" name="checkSum" value="{{ $room->checksum }}">
                        @foreach(($room->includedServices ?? []) as $serv)
                            <input type="hidden" name="servicesId" value="{{ $serv->id }}">
                        @endforeach
                        <input type="hidden" name="hotel" value="{{ $room->fullPlacementsName }}">
                        <input type="hidden" name="hotel_id" value="{{ $room->propertyId }}">
                        <input type="hidden" name="room_id" value="{{ $room->roomType->id }}">
                        <input type="hidden" name="title" value="{{ $room->fullPlacementsName }}">
                        <input type="hidden" name="price" value="{{ round($room->total->priceBeforeTax / 0.92) }}">
                        <button class="more">@lang('main.show_all_rooms')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>