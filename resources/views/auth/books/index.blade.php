@extends('auth.layouts.headbook')

@section('title', __('admin.bookings'))

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    @if(session()->has('success'))
                        <p class="alert alert-success">{{ session()->get('success') }}</p>
                    @endif
                    @if(session()->has('warning'))
                        <p class="alert alert-warning">{{ session()->get('warning') }}</p>
                    @endif
                    {{--                        <ul>--}}
                    {{--                            @foreach($books as $book)--}}
                    {{--                                <li>{{ $book->start_d }} - {{ $book->end_d }}</li>--}}
                    {{--                            @endforeach--}}
                    {{--                        </ul>--}}
                    @if($rooms->isNotEmpty())
                        <ul class="btns">
                            <li @routeactive(
                            'booking*')><a href="{{route('bookings.index')}}">@lang('admin.availability')</a></li>
                            <li @routeactive(
                            'price*')><a href="{{route('prices.index')}}">@lang('admin.prices')
                            </a></li>
                        </ul>
                        <div id='calendar'></div>
                        <div class
                             ="modal fade" tabindex="-1" role="dialog" id="show_modal">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3>@lang('admin.booking')</h3>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">@lang('admin.close')</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <form action="{{ route('listbooks.store') }}" method="post">
                                                    @csrf
                                                    <div class="form-group">
                                                        <input type="hidden" name="hotel_id"
                                                               value="{{$rooms->firstOrFail()->hotel_id}}">
                                                    </div>
                                                    <div class="form-group">
                                                        <input type="hidden" name="room_id" id="room_id">
                                                        <input type="hidden" class="form-control" name="title"
                                                               id="title" value="{{ Auth::user()->name }}"/>
                                                        <input type="hidden" class="form-control" name="email"
                                                               id="email" value="{{ Auth::user()->email }}"/>
                                                        <input type="hidden" class="form-control" name="sum"
                                                               id="email" value="1"/>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-xs-4" for="count">@lang('admin.quotes')
                                                        </label>
                                                        <select name="quote" id="quote" required>
                                                            <option value="0">0</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                            <option value="6">6</option>
                                                            <option value="7">7</option>
                                                            <option value="8">8</option>
                                                            <option value="9">9</option>
                                                            <option value="10">10</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        @lang('admin.date')
                                                        <input type="text" id="date">
                                                        <input type="hidden" id="start_d" name="start_d">
                                                        <input type="hidden" id="end_d" name="end_d">
                                                    </div>
                                                    <button class="more" id="saveBtn">@lang('admin.book')</button>

                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                    @else
                        <h2 style="text-align: center">@lang('admin.rates_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
