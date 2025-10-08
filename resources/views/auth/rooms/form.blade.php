@extends('auth.layouts.master')

@isset($room)
    @section('title', 'Edit ' . $room->title)
@else
    @section('title', 'Add Room')
@endisset

@section('content')

    <style>
        .amenities label {
            display: inline-block;
        }

        .img-item {
            margin: 10px 0;
            border-radius: 30px;
            border: 2px solid var(--green);
            background-color: #fafafa;
            text-align: center;
            transition: box-shadow 0.3s;
            max-width: 100%;
            object-fit: cover;
            height: 200px;
        }

        .img-item:hover {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }

        .ck-rounded-corners .ck.ck-editor__top .ck-sticky-panel .ck-toolbar, .ck.ck-editor__top .ck-sticky-panel .ck-toolbar.ck-rounded-corners {
            border-top-left-radius: 30px;
            border-top-right-radius: 30px;
        }

        .ck.ck-editor__main > .ck-editor__editable:not(.ck-focused) {
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
        }

        .select2-container--default .select2-selection--single {
            border-radius: 30px !important;
            height: 50px !important;
            border: none !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 50px !important;
            padding: 0px 15px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 50px;
            border: none;
        }
    </style>

    <div class="page admin">
        <div class="container-fluid">
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

                        <div class="row border-wrap">
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title_local'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title') Local</label>
                                    <input type="text" name="title_local" value="{{ old('title_local', isset($room) ? $room->title_local :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title_en'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title') Local EN</label>
                                    <input type="text" name="title_local_en" value="{{ old('title_en', isset($room) ?
                                $room->title_local_en :
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
                                @include('auth.layouts.error', ['fieldname' => 'area'])
                                <div class="form-group">
                                    <label for="">@lang('admin.area')</label>
                                    <input type="number" name="area" value="{{ old('area', isset($room) ?
                                    $room->area : null) }}">
                                </div>
                            </div>
                        </div>
                        @php
                            $amenityGroups = [
                                ['title' => 'Sports and Leisure', 'prefix' => 'sport', 'items' => [
                                    'Aquapark access', 'Barbecue', 'Fitness access', 'Golf access', 'Ski pass',
                                ]],
                                ['title' => 'General', 'prefix' => 'gen', 'items' => [
                                    'Heating', 'Safe', 'Poolside', 'Non-smoking', 'Pets allowed', 'Smoking',
                                ]],
                                ['title' => 'TV and Equipment', 'prefix' => 'tv', 'items' => [
                                    'Telephone', 'Conventional oven',
                                ]],
                                ['title' => 'Bathroom', 'prefix' => 'bath', 'items' => [
                                    'Bath','Bathroom','Toiletries','Hairdryer','Sauna','Shared bathroom',
                                    'Shared toilet','Slippers','Toilet','Jet tub','Shower stall',
                                    'External private bathroom','Private bathroom',
                                ]],
                                ['title' => 'Room Amenities', 'prefix' => 'ament', 'items' => [
                                    'TV','Bathrobe','Shower','Air conditioner','Wardrobe','Fireplace',
                                    'Connecting rooms available','Mosquito net','Private entrance','Sitting room',
                                    'Sofa','Soundproof','Desk','Minibar','Armchair','Coffee table',
                                    'Full-size mirror','Black-out curtains','High quality bed linen',
                                    'Allergy-friendly','Silverware','Fan',
                                ]],
                                ['title' => 'Extra Services', 'prefix' => 'ext', 'items' => [
                                    'Executive Lounge access','Towels/bed linen at surcharge',
                                    'Wake-up service /Alarm clock','Pillow menu','Accessible room',
                                    'Additional service','Bridal room',
                                ]],
                                ['title' => 'Food & Drink', 'prefix' => 'drink', 'items' => [
                                    'Dining area','Dishwasher','Electric kettle','Kitchen','Kitchenware',
                                    'Microwave oven','Refrigerator','Tea/coffee making facilities',
                                    'Kitchen stove','Bottled water','Kitchen supplies','Coffee maker/machine',
                                    'Tea or coffee',
                                ]],
                                ['title' => 'Outdoor/View', 'prefix' => 'view', 'items' => [
                                    'Balcony','Patio','Ocean view','City view','Beautiful view','Landmark view',
                                    'Room without window','Sea view','Mountain view','Terrace','Park view',
                                    'Lake view','Mansard',
                                ]],
                                ['title' => 'Laundry', 'prefix' => 'laun', 'items' => [
                                    'Iron','Ironing facilities','Washing machine','Drying',
                                ]],
                                ['title' => 'Pool and beach', 'prefix' => 'pool', 'items' => [
                                    'Beach','Beach access','Beachfront','Private beach','Private pool',
                                ]],
                                ['title' => 'Internet', 'prefix' => 'int', 'items' => [
                                    'High-speed internet access',
                                ]],
                                ['title' => 'Health and beauty', 'prefix' => 'spa', 'items' => [
                                    'Spa access',
                                ]],
                            ];
                        @endphp

                        @foreach ($amenityGroups as $group)
                            <div class="row">
                                <h6>{{ $group['title'] }}</h6>
                                @foreach ($group['items'] as $i => $label)
                                    @php $id = $group['prefix'] . ($i + 1); @endphp
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="{{ $id }}" class="option">
                                                <input id="{{ $id }}" type="checkbox" name="amenities[]"
                                                       value="{{ $label }}"
                                                @isset($room)
                                                    {{ in_array($label, $amenities ?? []) ? 'checked' : '' }}
                                                        @endisset>
                                                <span class="box" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" role="presentation"
                                                                 focusable="false">
                                                              <path d="M9.2 17.6c-.4 0-.8-.2-1.1-.5l-3.9-4a1.6 1.6 0 1 1 2.2-2.2l2.8 2.9 6.6-7a1.6 1.6 0 1 1 2.4 2.1l-7.7 8.2c-.3.3-.7.5-1.3.5z"/>
                                                            </svg>
                                                          </span>
                                                <span class="name">{{ $label }}</span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        <div class="form-group">
                            @include('auth.layouts.error', ['fieldname' => 'image'])
                            <label for="">@lang('admin.main_photo')</label>
                            @isset($room->image)
                                <img src="{{ Storage::url($room->image) }}" alt="" class="img-item">
                            @endisset
                            <input type="file" name="image">
                        </div>
                        <div class="form-group">
                            <label for="">@lang('admin.images')</label>
                            <input type="file" name="images[]" multiple="true">
                        </div>
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <button class="more">@lang('admin.send')</button>
                            </div>
                            <div class="col-md-6">
                                <a href="{{url()->previous()}}" class="btn delete cancel">@lang('admin.cancel')</a>
                            </div>
                        </div>
                    </form>

                    @isset($images)
                        <div class="images">
                            <div class="row">
                                <label for="">@lang('admin.images')</label>
                                @foreach($images as $image)
                                    <div class="col-md-6">
                                        <div class="img-item">
                                            <img src="{{ Storage::url($image->image) }}">
                                            <form action="{{ route('images.destroy', $image) }}"
                                                  method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn delete"
                                                        onclick="return confirm('Do you want to delete this?');"><img
                                                            src="{{ route('index')}}/img/icons/trash.svg" alt=""></button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endisset
                </div>
            </div>
        </div>
    </div>

@endsection
