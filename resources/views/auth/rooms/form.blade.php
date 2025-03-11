@extends('auth.layouts.master')

@isset($room)
    @section('title', 'Edit ' . $room->title)
@else
    @section('title', 'Add Room')
@endisset

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @isset($room)
                        <h1>@lang('admin.edit') {{ $room->title }}</h1>
                    @else
                        <h1>@lang('admin.add_room')</h1>
                    @endisset
                    <form method="post" enctype="multipart/form-data"
                          @isset($room)
                              action="{{ route('rooms.update', $room) }}"
                          @else
                              action="{{ route('rooms.store') }}"
                            @endisset
                    >
                        @isset($room)
                            @method('PUT')
                        @endisset
                        <input type="hidden" value="{{ $hotel }}" name="hotel_id">
                        <input type="hidden" name="user_id" value="{{ \Illuminate\Support\Facades\Auth::user()->id }}">
                        <div class="row">
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title')</label>
                                    <input type="text" name="title" value="{{ old('title', isset($room) ? $room->title :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title_en'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title') EN</label>
                                    <input type="text" name="title_en" value="{{ old('title_en', isset($room) ?
                                $room->title_en :
                             null) }}">
                                </div>
                            </div>
                        </div>
                        @include('auth.layouts.error', ['fieldname' => 'description'])
                        <div class="form-group">
                            <label for="">@lang('admin.description')</label>
                            <textarea name="description" id="editor" rows="3">{{ old('description', isset($room) ?
                            $room->description : null) }}</textarea>
                        </div>
                        @include('auth.layouts.error', ['fieldname' => 'description_en'])
                        <div class="form-group">
                            <label for="">@lang('admin.description') EN</label>
                            <textarea name="description_en" id="editor1" rows="3">{{ old('description_en', isset
                            ($room) ?
                            $room->description_en : null) }}</textarea>
                        </div>
                        <script src="https://cdn.tiny.cloud/1/yxonqgmruy7kchzsv4uizqanbapq2uta96cs0p4y91ov9iod/tinymce/6/tinymce.min.js"
                                referrerpolicy="origin"></script>
                        <script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
                        <script>
                            ClassicEditor
                                .create(document.querySelector('#editor'))
                                .catch(error => {
                                    console.error(error);
                                });
                            ClassicEditor
                                .create(document.querySelector('#editor1'))
                                .catch(error => {
                                    console.error(error);
                                });
                        </script>
                        <div class="row">
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'price'])
                                <div class="form-group">
                                    <label for="">Стоимость ($) за одного</label>
                                    <input type="number" name="price" value="{{ old('price', isset($room) ?
                                $room->price : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'price2'])
                                <div class="form-group">
                                    <label for="">Стоимость ($) за двоих</label>
                                    <input type="number" name="price2" value="{{ old('price2', isset($room) ?
                                $room->price2 : null) }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'area'])
                                <div class="form-group">
                                    <label for="">@lang('admin.categoryRooms')</label>
                                    <select name="category_id" id="">
                                        @foreach($cats as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'area'])
                                <div class="form-group">
                                    <label for="">@lang('admin.area')</label>
                                    <input type="number" name="area" value="{{ old('area', isset($room) ?
                                    $room->area :
                                null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'count'])
                                <div class="form-group">
                                    <label for="">@lang('admin.quotes')</label>
                                    <input type="number" name="count" value="{{ old('count', isset($room) ? $room->count :
                                null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'bed'])
                                    <label for="bed">@lang('admin.bed')</label>
                                    <select name="bed" id="bed">
                                        @isset($room)
                                            <option @if($room->bed)
                                                        selected>
                                                {{ $room->bed }}</option>
                                        @else
                                            <option>@lang('admin.choose')</option>
                                        @endif
                                        @endisset
                                        <option value="Single">Single</option>
                                        <option value="Double">Double</option>
                                        <option value="Twin">Twin</option>
                                        <option value="Triple">Triple</option>
                                        <option value="Quadruple">Quadruple</option>
                                        <option value="King Size">King Size</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>Sports and Leisure</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport1" type="checkbox" name="services[]" value="Aquapark access"
                                    @isset($room)
                                        {{ in_array('Aquapark access', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport1">Aquapark access</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport2" type="checkbox" name="services[]" value="Barbecue"
                                    @isset($room)
                                        {{ in_array('Barbecue', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport2">Barbecue</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport3" type="checkbox" name="services[]" value="Fitness access"
                                    @isset($room)
                                        {{ in_array('Fitness access', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport3">Fitness access</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport4" type="checkbox" name="services[]" value="Golf access"
                                    @isset($room)
                                        {{ in_array('Golf access', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport4">Golf access</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport5" type="checkbox" name="services[]" value="Ski pass"
                                    @isset($room)
                                        {{ in_array('Ski pass', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport5">Ski pass</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>General</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen1" type="checkbox" name="services[]" value="Heating"
                                    @isset($room)
                                        {{ in_array('Heating', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen1">Heating</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen2" type="checkbox" name="services[]" value="Safe"
                                    @isset($room)
                                        {{ in_array('Safe', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen2">Safe</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen3" type="checkbox" name="services[]" value="Poolside"
                                    @isset($room)
                                        {{ in_array('Poolside', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen3">Poolside</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen4" type="checkbox" name="services[]" value="Non-smoking"
                                    @isset($room)
                                        {{ in_array('Non-smoking', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen4">Non-smoking</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen5" type="checkbox" name="services[]" value="Pets allowed"
                                    @isset($room)
                                        {{ in_array('Pets allowed', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen5">Pets allowed</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen" type="checkbox" name="services[]" value="Smoking"
                                    @isset($room)
                                        {{ in_array('Smoking', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen">Smoking</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>TV and Equipment</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="tv" type="checkbox" name="services[]" value="Telephone"
                                    @isset($room)
                                        {{ in_array('Telephone', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="tv">Telephone</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="conv" type="checkbox" name="services[]" value="Conventional oven"
                                    @isset($room)
                                        {{ in_array('Conventional oven', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="conv">Conventional oven</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>Bathroom</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath1" type="checkbox" name="services[]" value="Bath"
                                    @isset($room)
                                        {{ in_array('Bath', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath1">Bath</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath2" type="checkbox" name="services[]" value="Bathroom"
                                    @isset($room)
                                        {{ in_array('Bathroom', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath2">Bathroom</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath3" type="checkbox" name="services[]" value="Toiletries"
                                    @isset($room)
                                        {{ in_array('Toiletries', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath3">Toiletries</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath4" type="checkbox" name="services[]" value="Hairdryer"
                                    @isset($room)
                                        {{ in_array('Hairdryer', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath4">Hairdryer</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath5" type="checkbox" name="services[]" value="Sauna"
                                    @isset($room)
                                        {{ in_array('Sauna', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath5">Sauna</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath6" type="checkbox" name="services[]" value="Shared bathroom"
                                    @isset($room)
                                        {{ in_array('Shared bathroom', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath6">Shared bathroom</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath7" type="checkbox" name="services[]" value="Shared toilet"
                                    @isset($room)
                                        {{ in_array('Shared toilet', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath7">Shared toilet</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath8" type="checkbox" name="services[]" value="Slippers"
                                    @isset($room)
                                        {{ in_array('Slippers', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath8">Slippers</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath9" type="checkbox" name="services[]" value="Toilet"
                                    @isset($room)
                                        {{ in_array('Toilet', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath9">Toilet</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath10" type="checkbox" name="services[]" value="Jet tub"
                                    @isset($room)
                                        {{ in_array('Jet tub', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath10">Jet tub</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath11" type="checkbox" name="services[]" value="Shower stall"
                                    @isset($room)
                                        {{ in_array('Shower stall', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath11">Shower stall</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath12" type="checkbox" name="services[]" value="External private
                                    bathroom"
                                    @isset($room)
                                        {{ in_array('External private bathroom', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath12">External private bathroom</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bath13" type="checkbox" name="services[]" value="Private bathroom"
                                    @isset($room)
                                        {{ in_array('Private bathroom', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="bath13">Private bathroom</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>Room Amenities</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament1" type="checkbox" name="services[]" value="TV"
                                    @isset($room)
                                        {{ in_array('TV', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament1">TV</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament2" type="checkbox" name="services[]" value="Bathrobe"
                                    @isset($room)
                                        {{ in_array('Bathrobe', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament2">Bathrobe</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament3" type="checkbox" name="services[]" value="Shower"
                                    @isset($room)
                                        {{ in_array('Shower', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament3">Shower</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament4" type="checkbox" name="services[]" value="Air conditioner"
                                    @isset($room)
                                        {{ in_array('Air conditioner', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament4">Air conditioner</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament5" type="checkbox" name="services[]" value="Wardrobe"
                                    @isset($room)
                                        {{ in_array('Wardrobe', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament5">Wardrobe</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-gimaroup">
                                    <input id="ament6" type="checkbox" name="services[]" value="Fireplace"
                                    @isset($room)
                                        {{ in_array('Fireplace', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament6">Fireplace</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament7" type="checkbox" name="services[]"
                                           value="Connecting rooms available"
                                    @isset($room)
                                        {{ in_array('Connecting rooms available', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament7">Connecting rooms available</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament8" type="checkbox" name="services[]" value="Mosquito net"
                                    @isset($room)
                                        {{ in_array('Mosquito net', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament8">Mosquito net</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament9" type="checkbox" name="services[]" value="Private entrance"
                                    @isset($room)
                                        {{ in_array('Private entrance', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament9">Private entrance</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament10" type="checkbox" name="services[]" value="Sitting room"
                                    @isset($room)
                                        {{ in_array('Sitting room', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament10">Sitting room</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament11" type="checkbox" name="services[]" value="Sofa"
                                    @isset($room)
                                        {{ in_array('Sofa', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament11">Sofa</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament12" type="checkbox" name="services[]" value="Soundproof"
                                    @isset($room)
                                        {{ in_array('Soundproof', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament12">Soundproof</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament13" type="checkbox" name="services[]" value="Desk"
                                    @isset($room)
                                        {{ in_array('Desk', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament13">Desk</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament14" type="checkbox" name="services[]" value="Minibar"
                                    @isset($room)
                                        {{ in_array('Minibar', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament14">Minibar</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament15" type="checkbox" name="services[]" value="Armchair"
                                    @isset($room)
                                        {{ in_array('Armchair', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament15">Armchair</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament16" type="checkbox" name="services[]" value="Coffee table"
                                    @isset($room)
                                        {{ in_array('Coffee table', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament16">Coffee table</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament17" type="checkbox" name="services[]" value="Full-size mirror"
                                    @isset($room)
                                        {{ in_array('Full-size mirror', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament17">Full-size mirror</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament18" type="checkbox" name="services[]" value="Black-out curtains"
                                    @isset($room)
                                        {{ in_array('Black-out curtains', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament18">Black-out curtains</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament19" type="checkbox" name="services[]" value="High quality bed linen"
                                    @isset($room)
                                        {{ in_array('High quality bed linen', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament19">High quality bed linen</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament20" type="checkbox" name="services[]" value="Allergy-friendly"
                                    @isset($room)
                                        {{ in_array('Allergy-friendly', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament20">Allergy-friendly</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament21" type="checkbox" name="services[]" value="Silverware"
                                    @isset($room)
                                        {{ in_array('Silverware', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament21">Silverware</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ament22" type="checkbox" name="services[]" value="Fan"
                                    @isset($room)
                                        {{ in_array('Fan', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ament22">Fan</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>Extra Services</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ext1" type="checkbox" name="services[]" value="Executive Lounge access"
                                    @isset($room)
                                        {{ in_array('Executive Lounge access', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ext1">Executive Lounge access</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ext2" type="checkbox" name="services[]"
                                           value="Towels/bed linen at surcharge"
                                    @isset($room)
                                        {{ in_array('Towels/bed linen at surcharge', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ext2">Towels/bed linen at surcharge</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ext3" type="checkbox" name="services[]"
                                           value="Wake-up service /Alarm clock"
                                    @isset($room)
                                        {{ in_array('Wake-up service /Alarm clock', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ext3">Wake-up service /Alarm clock</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ext4" type="checkbox" name="services[]" value="Pillow menu"
                                    @isset($room)
                                        {{ in_array('Pillow menu', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ext4">Pillow menu</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ext5" type="checkbox" name="services[]" value="Accessible room"
                                    @isset($room)
                                        {{ in_array('Accessible room', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ext5">Accessible room</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ext6" type="checkbox" name="services[]" value="Additional service"
                                    @isset($room)
                                        {{ in_array('Additional service', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ext6">Additional service</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ext7" type="checkbox" name="services[]" value="Bridal room"
                                    @isset($room)
                                        {{ in_array('Bridal room', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ext7">Bridal room</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>Food & Drink</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink1" type="checkbox" name="services[]" value="Dining area"
                                    @isset($room)
                                        {{ in_array('Dining area', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink1">Dining area</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink2" type="checkbox" name="services[]" value="Dishwasher"
                                    @isset($room)
                                        {{ in_array('Dishwasher', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink2">Dishwasher</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink3" type="checkbox" name="services[]" value="Electric kettle"
                                    @isset($room)
                                        {{ in_array('Electric kettle', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink3">Electric kettle</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink4" type="checkbox" name="services[]" value="Kitchen"
                                    @isset($room)
                                        {{ in_array('Kitchen', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink4">Kitchen</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink5" type="checkbox" name="services[]" value="Kitchenware"
                                    @isset($room)
                                        {{ in_array('Kitchenware', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink5">Kitchenware</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink6" type="checkbox" name="services[]" value="Microwave oven"
                                    @isset($room)
                                        {{ in_array('Microwave oven', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink6">Microwave oven</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink7" type="checkbox" name="services[]" value="Refrigerator"
                                    @isset($room)
                                        {{ in_array('Refrigerator', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink7">Refrigerator</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink8" type="checkbox" name="services[]"
                                           value="Tea/coffee making facilities"
                                    @isset($room)
                                        {{ in_array('Tea/coffee making facilities', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink8">Tea/coffee making facilities</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink9" type="checkbox" name="services[]" value="Kitchen stove"
                                    @isset($room)
                                        {{ in_array('Kitchen stove', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink9">Kitchen stove</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink10" type="checkbox" name="services[]" value="Bottled water"
                                    @isset($room)
                                        {{ in_array('Bottled water', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink10">Bottled water</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink11" type="checkbox" name="services[]" value="Kitchen supplies"
                                    @isset($room)
                                        {{ in_array('Kitchen supplies', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink11">Kitchen supplies</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink12" type="checkbox" name="services[]" value="Coffee maker/machine"
                                    @isset($room)
                                        {{ in_array('Coffee maker/machine', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink12">Coffee maker/machine</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="drink13" type="checkbox" name="services[]" value="Tea or coffee"
                                    @isset($room)
                                        {{ in_array('Tea or coffee', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="drink13">Tea or coffee</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>Outdoor/View</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view1" type="checkbox" name="services[]" value="Balcony"
                                    @isset($room)
                                        {{ in_array('Balcony', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view1">Balcony</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view2" type="checkbox" name="services[]" value="Patio"
                                    @isset($room)
                                        {{ in_array('Patio', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view2">Patio</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view3" type="checkbox" name="services[]" value="Ocean view"
                                    @isset($room)
                                        {{ in_array('Ocean view', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view3">Ocean view</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view4" type="checkbox" name="services[]" value="City view"
                                    @isset($room)
                                        {{ in_array('City view', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view4">City view</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view5" type="checkbox" name="services[]" value="Beautiful view"
                                    @isset($room)
                                        {{ in_array('Beautiful view', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view5">Beautiful view</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view6" type="checkbox" name="services[]" value="Landmark view"
                                    @isset($room)
                                        {{ in_array('Landmark view', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view6">Landmark view</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view7" type="checkbox" name="services[]" value="Room without window"
                                    @isset($room)
                                        {{ in_array('Room without window', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view7">Room without window</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view8" type="checkbox" name="services[]" value="Sea view"
                                    @isset($room)
                                        {{ in_array('Sea view', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view8">Sea view</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view9" type="checkbox" name="services[]" value="Mountain view"
                                    @isset($room)
                                        {{ in_array('Mountain view', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view9">Mountain view</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view10" type="checkbox" name="services[]" value="Terrace"
                                    @isset($room)
                                        {{ in_array('Terrace', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view10">Terrace</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view11" type="checkbox" name="services[]" value="Park view"
                                    @isset($room)
                                        {{ in_array('Park view', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view11">Park view</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view12" type="checkbox" name="services[]" value="Lake view"
                                    @isset($room)
                                        {{ in_array('Lake view', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view12">Lake view</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="view13" type="checkbox" name="services[]" value="Mansard"
                                    @isset($room)
                                        {{ in_array('Mansard', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="view13">Mansard</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>Laundry</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="laun1" type="checkbox" name="services[]" value="Iron"
                                    @isset($room)
                                        {{ in_array('Iron', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="laun1">Iron</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="laun2" type="checkbox" name="services[]" value="Ironing facilities"
                                    @isset($room)
                                        {{ in_array('Ironing facilities', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="laun2">Ironing facilities</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="laun3" type="checkbox" name="services[]" value="Washing machine"
                                    @isset($room)
                                        {{ in_array('Washing machine', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="laun3">Washing machine</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="laun4" type="checkbox" name="services[]" value="Drying"
                                    @isset($room)
                                        {{ in_array('Drying', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="laun4">Drying</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>Pool and beach</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="pool1" type="checkbox" name="services[]" value="Beach"
                                    @isset($room)
                                        {{ in_array('Beach', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="pool1">Beach</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="pool2" type="checkbox" name="services[]" value="Beach access"
                                    @isset($room)
                                        {{ in_array('Beach access', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="pool2">Beach access</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="pool3" type="checkbox" name="services[]" value="Beachfront"
                                    @isset($room)
                                        {{ in_array('Beachfront', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="pool3">Beachfront</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="pool4" type="checkbox" name="services[]" value="Private beach"
                                    @isset($room)
                                        {{ in_array('Private beach', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="pool4">Private beach</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="pool5" type="checkbox" name="services[]" value="Private pool"
                                    @isset($room)
                                        {{ in_array('Private pool', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="pool5">Private pool</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>Internet</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="int" type="checkbox" name="services[]" value="High-speed internet access"
                                    @isset($room)
                                        {{ in_array('High-speed internet access', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="int">High-speed internet access</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>Health and beauty
                            </h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="spa" type="checkbox" name="services[]" value="Spa access"
                                    @isset($room)
                                        {{ in_array('Spa access', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="spa">Spa access</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            @include('auth.layouts.error', ['fieldname' => 'image'])
                            <label for="">@lang('admin.main_photo')</label>
                            @isset($room->image)
                                <img src="{{ Storage::url($room->image) }}" alt="">
                            @endisset
                            <input type="file" name="image">
                        </div>
                        <div class="form-group">
                            <label for="">Загрузить изображения</label>
                            <input type="file" name="images[]" multiple="true">
                        </div>
                        <div class="form-group">
                            <label for="">@lang('admin.status')</label>
                            <select name="status">
                                @if(isset($room))
                                    @if($room->status == 1)
                                        <option value="{{$room->status}}">@lang('admin.active')</option>
                                        <option value="0">@lang('admin.disable')</option>
                                    @else
                                        <option value="{{$room->status}}">@lang('admin.disable')</option>
                                        <option value="1">@lang('admin.active')</option>
                                    @endif
                                @else
                                    <option value="1">@lang('admin.active')</option>
                                    <option value="0">@lang('admin.disable')</option>
                                @endif
                            </select>
                        </div>
                        @csrf
                        <button class="more">@lang('admin.send')</button>
                        <a href="{{url()->previous()}}" class="btn delete cancel">@lang('admin.cancel')</a>
                    </form>

                    <div class="img-wrap">
                        <div class="row">
                            <label for="">Все изображения</label>
                            @isset($images)
                                @foreach($images as $image)
                                    <div class="col-md-2">
                                        <div class="img-item">
                                            <img src="{{ Storage::url($image->image) }}" alt="">
                                            <form action="{{ route('images.destroy', $image) }}"
                                                  method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn delete"
                                                        onclick="return confirm('Do you want to delete this?');"><i
                                                            class="fa-regular
                                                    fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @endisset
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .admin label {
            display: inline-block;
        }

        .img-wrap {
            margin-top: 30px;
        }

        .img-item {
            position: relative;
        }
    </style>

@endsection
