@php
    use App\Models\Image;$rawHotel   = $item['hotel'] ?? null;
    $h          = is_object($rawHotel) ? $rawHotel : (is_array($rawHotel) ? (object)$rawHotel : null);
    $title      = $h->title ?? ($rawHotel['title'] ?? '');
    $images     = $h?->id ? Image::where('hotel_id', $h->id)->take(2)->get() : collect();
    $amenityObj = $h?->id ? \App\Models\Amenity::where('hotel_id', $h->id)->first() : null;
    $amenities  = $amenityObj && $amenityObj->services ? explode(',', $amenityObj->services) : [];
    $itemsAmen  = array_slice($amenities, 0, 8);
    $converted  = $item['conv_total'] ?? 0;
    $symbol     = $item['conv_symbol'] ?? '';
    $price      = $item['price'] ?? 0;
@endphp

<div class="search-item"
     data-type="{{ $h->type ?? '' }}"
     data-id="{{ $h->id ?? '' }}"
     data-title="{{ strtolower($title) }}"
     data-price="{{ $price }}">
    <div class="row">
        <div class="col-md-6 order-1">
            <div class="img-wrap">
                <div class="owl-carousel owl-slider">
                    <div class="slider-item">
                        @if(!empty($h?->image))
                            <img src="{{ Storage::url($h->image) }}" alt="">
                        @elseif($images->isNotEmpty())
                            @foreach($images as $file)
                                <div class="primary"><img src="{{ Storage::url($file->image) }}" alt=""></div>
                            @endforeach
                        @else
                            <img src="{{ route('index') }}/img/noimage.png" alt="">
                        @endif
                    </div>
                </div>
                <div class="text-wrap">
                    <div class="price">@lang('main.from') {{ number_format((int)$converted, 0, '.', ' ') }} {{ $symbol }}</div>
                    <div class="night">@lang('main.night')</div>
                    @if(!empty($h?->rating))
                        <div class="rating">{{ $h->rating }} <img src="{{ route('index') }}/img/star.svg" alt=""></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6 order-2">
            <div class="wrap">
                <h4>{{ $h?->__('title') }}</h4>
                <div class="amenities">
                    @foreach($itemsAmen as $amenity)
                        <div class="amenities-item">
                            <img src="{{ asset('img/icons/check.svg') }}" alt="{{ $amenity }}">
                            <div class="name">{{ $amenity }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="address">{{ $h?->__('address') }}</div>
                <div class="btn-wrap">
                    <form action="{{ route('findHotel', $h->code) }}">
                        <input type="hidden" name="arrivalDate" value="{{ request('arrivalDate') }}">
                        <input type="hidden" name="departureDate" value="{{ request('departureDate') }}">
                        <input type="hidden" name="roomCount" value="{{ $roomCount ?: 1 }}">
                        <input type="hidden" name="adult" value="{{ $totalAdults ?: 1 }}">
                        <input type="hidden" name="child" value="{{ $totalChildren ?: 0 }}">
                        <input type="hidden" name="childAges[]"
                               value="{{ implode(', ', (array) request('childAges', [])) }}">
                        @foreach((array) request('meal') as $meal)
                            <input type="hidden" name="meal[]" value="{{ $meal }}">
                        @endforeach
                        <button class="more">@lang('main.show_all_rooms')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- EXELY --}}