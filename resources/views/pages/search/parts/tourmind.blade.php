@php
    $tm       = $item['tm'] ?? [];
    $hotelObj = $item['hotel'] ?? null;

    $title    = $hotelObj?->title ?? ($tm->title ?? ($tm->localData->title ?? ''));
    $images   = $hotelObj ? Image::where('hotel_id', $hotelObj->id)->orderBy('id')->limit(2)->get() : collect();
    $tmImages = collect($tm->images ?? ($tm->localData->images ?? []));
    $amenStr  = $hotelObj && $hotelObj->amenity ? $hotelObj->amenity->services : ($tm->localData->amenity->services ?? '');
    $amenities= $amenStr ? explode(',', $amenStr) : [];
    $itemsAmen= array_slice($amenities, 0, 8);

    $converted = $item['conv_total'] ?? 0;
    $symbol    = $item['conv_symbol'] ?? '';
    $price     = $item['price'] ?? 0;
    $dataId    = $hotelObj?->id ?? ($tm->hid ?? '');
@endphp

<div class="search-item tm"
     data-id="{{ $dataId }}"
     data-type="{{ $hotelObj?->type ?? '' }}"
     data-title="{{ strtolower($title) }}"
     data-price="{{ $price }}">
    <div class="row">
        <div class="col-md-5 order-1">
            <div class="img-wrap">
                @if($images->count() >= 1)
                    <div class="row">
                        <div class="col-md-6 col-6">
                            <div class="main"><img src="{{ Storage::url($images[0]->image) }}" alt=""></div>
                        </div>
                        <div class="col-md-6 col-6">
                            @if($images->count() >= 2)
                                <div class="primary"><img src="{{ Storage::url($images[1]->image) }}" alt=""></div>
                            @endif
                        </div>
                    </div>
                @elseif($tmImages->count() >= 1)
                    <img src="{{ $tmImages->first() }}" alt="">
                @else
                    @if($hotelObj?->image)
                        <img src="{{ Storage::url($hotelObj->image) }}" alt="">
                    @else
                        <img src="{{ route('index') }}/img/noimage.png" alt="">
                    @endif
                @endif
            </div>
        </div>

        <div class="col-md-7 order-2">
            <div class="row">
                <div class="col-md-7 col-8">
                    <h4>{{ $title }}</h4>
                    @if($hotelObj?->rating)
                        <div class="rating"><img src="{{ route('index') }}/img/star.svg" alt=""> {{ $hotelObj->rating }}</div>
                    @endif
                </div>
                <div class="col-md-5 col-4">
                    <div class="price">@lang('main.from') {{ number_format((int)$converted, 0, '.', ' ') }} {{ $symbol }}</div>
                    <div class="night">@lang('main.night')</div>
                </div>
            </div>
            <div class="amenities">
                @foreach($itemsAmen as $amenity)
                    <div class="amenities-item">
                        <img src="{{ asset('img/icons/check.svg') }}" alt="{{ $amenity }}">
                        <div class="name">{{ $amenity }}</div>
                    </div>
                @endforeach
            </div>
            <div class="address">{{ $hotelObj?->__("address") }}</div>
            <div class="btn-wrap">
                <form action="{{ route('findHotel', $hotelObj?->code) }}">
                    <input type="hidden" name="arrivalDate"  value="{{ request('arrivalDate') }}">
                    <input type="hidden" name="departureDate" value="{{ request('departureDate') }}">
                    <button class="more">@lang('main.show_all_rooms')</button>
                </form>
            </div>
        </div>
    </div>
</div>