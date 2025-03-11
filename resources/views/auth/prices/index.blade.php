@extends('auth.layouts.headprice')

@section('title', 'Бронирование')

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
                    @if($rooms->isNotEmpty())
                        <ul class="btns">
                            <li @routeactive(
                            'booking*')><a href="{{route('bookings.index')}}">@lang('admin.availability')</a></li>
                            <li @routeactive(
                            'price*')><a href="{{route('prices.index')}}">@lang('admin.prices')
                            </a></li>
                        </ul>
                        <div id='calendar'></div>
                        <div class="modal fade" tabindex="-1" role="dialog" id="show_modal">
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
                                                <form action="{{ route('prices.store') }}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="hotel_id"
                                                           value="{{$hotel_id}}">
                                                    <input type="hidden" name="room_id" id="room_id">
                                                    <input type="hidden" class="form-control" name="title"
                                                           id="title" value="{{ Auth::user()->name }}"/>
                                                    <input type="hidden" class="form-control" name="email"
                                                           id="email" value="{{ Auth::user()->email }}"/>
                                                    <input type="hidden" class="form-control" name="phone"
                                                           id="phone" value="{{ Auth::user()->phone }}"/>
                                                    <input type="hidden" class="form-control" name="sum" value="1"/>
                                                    <input type="hidden" class="form-control" name="adult" value="1"/>
                                                    <div class="form-group">
                                                        <label class="col-xs-4" for="count">@lang('admin.price')</label>
                                                        <input type="text" name="price">
                                                    </div>
                                                    <div class="form-group">
                                                        <input type="text" id="date">
                                                        <input type="hidden" id="arrival" name="arrivalDate">
                                                        <input type="hidden" id="departure" name="departureDate">
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
