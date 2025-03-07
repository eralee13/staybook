<div class="row rooms-item">
    <div class="col-lg-4 col-md-6" data-aos="fade-right" data-aos-duration="2000">
        <a href="">
            <div class="img" style="background-image: url({{ Storage::url($category->room->image) }})"></div>
        </a>
    </div>
    <div class="col-lg-6 col-md-6">
        <div class="start">@lang('main.start_d') {{ $category->hotel->checkin ?? '' }}</div>
        <div class="end">@lang('main.end_d') {{ $category->hotel->checkout ?? '' }}</div>
        <div class="title">{{ $category->hotel->__('title') ?? '' }}</div>
        <h3>{{ $category->room->__('title') ?? '' }}</h3>
        <h5>{{ $category->__('title') }}</h5>
        <div class="address">{{ $category->hotel->__('address') ?? '' }}</div>
        <div class="d-xl-none d-lg-none d-block">
            <div class="price">
                <td>
                    $ {{ $category->room->price }}
                </td>
            </div>
            @if($category->food->title != '')
                <div class="breakfast">{{ $category->food->title }}</div>
            @endif
        </div>
        <div class="btn-wrap">
            <a href="{{ route('room', [isset($hotel) ? $hotel->code ?? '' : $category->room->hotel->code ?? '',
                    $category->room->code])
            }}" class="more read">@lang('main.more')</a>
            <a href="#" class="book-item more">@lang('main.book')
                <div class="hidden">
                    <div class="book-popup">
                        <form action="{{ route('book_mail') }}" method="post"
                              id="callback"
                              class="form-callback">
                            @csrf
                            <h3>@lang('main.book') {{ $category->room->__('title') ?? '' }} <br> {{
                                    $category->room->hotel->__('title')
                            ?? ''
                            }}</h3>
                            <input type="hidden" name="room_id" value="{{ $category->room->id}}">
                            <input type="hidden" name="hotel_id" value="{{ $category->hotel->id ?? ''}}">
                            @if($request->start_d != '' && $request->end_d != '')
                                <input type="hidden" id="start_d" name="start_d"
                                       value="{{ $request->start_d }}">
                                <input type="hidden" id="end_d" name="end_d"
                                       value="{{ $request->end_d }}">
                            @else
                                <div class="form-group">
                                    <label class="col-xs-4" for="end_d">@lang('main.date')</label>
                                    <input type="text" id="date" class="date">
                                    <input type="hidden" id="start_d"
                                           @php
                                               $now = \Carbon\Carbon::now();
                                               $date = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $now);
                                                  //$date = date('Y-m-d H:s:i');
                                           @endphp
                                           name="start_d" value="{{ date('Y-m-d H:s:i') }}">
                                    <input type="hidden" id="end_d" name="end_d"
                                           value="{{ $date->addDays(1) }}">
                                </div>
                            @endif

                            @if($request->count == 1)
                                <div class="form-group">
                                    <label class="col-xs-4" for="title">@lang('main.adult_name1')</label>
                                    <input type="text" class="form-control" name="title"
                                           id="title"
                                    />
                                </div>
                                <input type="hidden" name="count" value="{{ $request->count }}">
                            @elseif($request->count == 2)
                                <input type="hidden" name="count" value="{{ $request->count }}">
                                <div class="form-group">
                                    <label class="col-xs-4" for="title">@lang('main.adult_name1')</label>
                                    <input type="text" class="form-control" name="title"
                                           id="title"
                                    />
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-4" for="title2">@lang('main.adult_name2')</label>
                                    <input type="text" class="form-control" name="title2"
                                           id="title2"/>
                                </div>
                                <style>
                                    #title2 {
                                        display: block;
                                    }
                                </style>
                            @else
                                <div class="form-group">
                                    <label class="col-xs-4" for="count">@lang('main.search-count')</label>
                                    <select name="count" id="count" onchange="countCheck(this);" required>
                                        <option value="">@lang('main.choose')</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                    </select>
                                </div>

                                <div class="form-group" id="title">
                                    <label class="col-xs-4" for="title">@lang('main.adult_name1')</label>
                                    <input type="text" class="form-control" name="title"
                                           id="title" required/>
                                </div>
                                <div class="form-group" id="title2">
                                    <label class="col-xs-4" for="title2">@lang('main.adult_name2')</label>
                                    <input type="text" class="form-control" name="title2"/>
                                </div>
                                <style>
                                    #title2, #child1, #child2, #child3 {
                                        display: none;
                                    }
                                </style>
                            @endif

                            @if($request->countc == 1)
                                <input type="hidden" name="countc" class="countc" id="countc"
                                       value="{{ $request->countc }}">
                                <input type="hidden" name="age1" value="{{ $request->age1 }}">
                                <div class="form-group">
                                    <label class="col-xs-4" for="titlec1">@lang('main.child_name1') ({{
                                            $request->age1 }}
                                        )
                                    </label>
                                    <input type="text" class="form-control" name="titlec1"
                                           id="titlec1"/>
                                </div>
                            @elseif($request->countc == 2)
                                <input type="hidden" name="countc" class="countc" id="countc"
                                       value="{{ $request->countc }}">
                                <input type="hidden" name="age1" value="{{ $request->age1 }}">
                                <input type="hidden" name="age2" value="{{ $request->age2 }}">
                                <div class="form-group">
                                    <label class="col-xs-4" for="titlec1">@lang('main.child_name1') ({{
                                            $request->age1 }}
                                        )
                                    </label>
                                    <input type="text" class="form-control" name="titlec1"
                                           id="titlec1"/>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-4" for="titlec2">@lang('main.child_name2') ({{
                                            $request->age2 }}
                                        )
                                    </label>
                                    <input type="text" class="form-control" name="titlec2"
                                           id="titlec2"/>
                                </div>
                            @elseif($request->countc == 3)
                                <input type="hidden" name="countc" class="countc" id="countc"
                                       value="{{ $request->countc }}">
                                <input type="hidden" name="age1" value="{{ $request->age1 }}">
                                <input type="hidden" name="age2" value="{{ $request->age2 }}">
                                <input type="hidden" name="age3" value="{{ $request->age3 }}">
                                <div class="form-group">
                                    <label class="col-xs-4" for="titlec1">@lang('main.child_name1') ({{
                                            $request->age1 }}
                                        )</label>
                                    <input type="text" class="form-control" name="titlec1"
                                           id="titlec1" required/>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-4" for="titlec2">@lang('main.child_name2') ({{
                                            $request->age2 }}
                                        )
                                    </label>
                                    <input type="text" class="form-control" name="titlec2"
                                           id="titlec2"/>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-4" for="titlec3">@lang('main.child_name3')
                                        ({{ $request->age3 }}
                                        )
                                    </label>
                                    <input type="text" class="form-control" name="titlec3"
                                           id="titlec3"/>
                                </div>
                            @endif

                            <div class="form-group">
                                <label class="col-xs-4" for="phone">@lang('main.phone')</label>
                                <input type="text" class="form-control"
                                       name="phone" id="phone" required
                                >
                                <div id="output" class="output"></div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-4" for="email">Email</label>
                                <input type="email" class="form-control"
                                       name="email" id="email" required/>
                            </div>

                            <input type="hidden" id="price" class="price" value="{{ $category->room->price }}">
                            <input type="hidden" id="price2" class="price2" value="{{ $category->room->price2
                                    }}">

                            @php
                                $start = \Carbon\Carbon::parse($request->start_d);
                                $end = \Carbon\Carbon::parse($request->end_d);
                                $days = $end->diffInDays($start);
                                if($days == 0){
                                    $days = 1;
                                } else {
                                    $days = $end->diffInDays($start);
                                }
                                    $countc = $request->countc;
                                    $count = $request->count;
                                    $price = $category->room->price;
                                    $price2 = $category->room->price2;

                                    $age1 = $request->age1;
                                    $age2 = $request->age2;
                                    $age3 = $request->age3;
                                    $from = $child->age_from ?? 0;
                                    $from2 = $child->age_from2 ?? 0;
                                    $from3 = $child->age_from3 ?? 0;

                                    $to = $child->age_to ?? 1;
                                    $to2 = $child->age_to2 ?? 1;
                                    $to3 = $child->age_to3 ?? 1;

                                    //cat age1
                                    if ($age1 >= $from && $age1 <= $to)
                                    {
                                        $pricec = $child->price_extra ?? 0;
                                    }
                                    elseif ($age1 >= $from2 && $age1 <= $to2) {
                                        $pricec = $child->price_extra2 ?? 0;
                                    }
                                    elseif ($age1 >= $from3 && $age1 <= $to3)
                                    {
                                        $pricec = $child->price_extra3 ?? 0;
                                    } else{
                                        $pricec = $child->price_extra ?? 0;
                                    }

                                    //cat age2
                                    if ($age2 >= $from && $age2 <= $to)
                                    {
                                        $pricec2 = $child->price_extra ?? 0;
                                    } elseif ($age2 >= $from2 && $age2 <= $to2) {
                                        $pricec2 = $child->price_extra2 ?? 0;
                                    } elseif ($age2 >= $from3 && $age2 <= $to3)
                                    {
                                        $pricec2 = $child->price_extra3 ?? 0;
                                    } else{
                                        $pricec2 = $child->price_extra ?? 0;
                                    }

                                    //cat age1
                                    if ($age3 >= $from && $age3 <= $to)
                                    {
                                        $pricec3 = $child->price_extra ?? 0;
                                    } elseif ($age3 >= $from2 && $age3 <= $to2) {
                                        $pricec3 = $child->price_extra2 ?? 0;
                                    } elseif ($age3 >= $from3 && $age3 <= $to3)
                                    {
                                        $pricec3 = $child->price_extra3 ?? 0;
                                    } else{
                                        $pricec3 = $child->price_extra ?? 0;
                                    }

                                    if($countc == 2){
                                        $sum = $price * $days + (($pricec + $pricec2) * $days);
                                        $sum2 = $price2 * $days + (($pricec + $pricec2) * $days);
                                    } elseif($countc == 3) {
                                        $sum = $price * $days + (($pricec + $pricec2 + $pricec3) * $days);
                                        $sum2 = $price2 * $days + (($pricec + $pricec2 + $pricec3) * $days);
                                    } elseif($countc == null) {
                                        $sum = $price * $days + 0 * $days;
                                        $sum2 = $price2 * $days + 0 * $days;
                                    } else {
                                        $sum = $price * $days + $pricec * $days;
                                        $sum2 = $price2 * $days + $pricec * $days;
                                    }
                            @endphp

                            <div class="form-group">
                                <label for="">@lang('main.sum')</label>
                                @if ($count == 2)
                                    <input type="text" id="sum" name="sum" class="sum" value="$ {{ $sum2 }}"
                                           readonly>
                                @else
                                    <input type="text" id="sum" name="sum" class="sum" value="$ {{ $sum }}"
                                           readonly>
                                @endif

                            </div>
                            <div class="form-group">
                                @include('auth.layouts.error', ['fieldname' => 'comment'])
                                <label for="">Комментарий</label>
                                <textarea name="comment" rows="3"></textarea>
                            </div>
                            <input type="hidden" name="status" value="@lang('main.paid')">
                            <button class="more" id="saveBtn">@lang('main.book')</button>
                        </form>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="col-lg-2 d-xl-block d-lg-block d-none" data-aos="fade-left" data-aos-duration="2000">
        <div class="price">
            <td>
                $ {{ $category->room->price }}
            </td>
        </div>
        @if($category->food->title != '')
            <div class="breakfast">{{ $category->food->title }}</div>
        @endif
        @if($category->room->hotel->early_in ?? '')
            <div class="early">@lang('main.early')</div>
        @endif
        @if($category->room->hotel->early_out ?? '')
            <div class="early">@lang('main.late')</div>
        @endif
        @if($category->room->bed != '')
            <p><i class="fa-light fa-bed"></i> @lang('main.bed'): {{$category->room->bed}}</p>
        @endif
        @if($category->hotel->cancelled ?? '')
            @isset($end)
                @php
                    $end = $_GET['end_d'] ?? null;
                    $date = \Carbon\Carbon::parse($end);
                    $date->locale('ru');
                    $exp = $date->subDays($category->room->cancel_day);
                    $month = $exp->getTranslatedMonthName('Do MMMM');
                    $get = $exp->day . ' ' . $month;
                @endphp
                <div class="early">@lang('main.cancelled') до {{ $get }} </div>
            @endisset
        @endif

    </div>
</div>