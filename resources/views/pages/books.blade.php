@extends('layouts.booking')

@section('title', 'Бронирование')

@section('content')

<div class="page booking">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 offset-lg-1 col-md-12">
                <h1>{{ $room->__('title') }}</h1>
                <div id='calendar'></div>
            </div>
        </div>
        <div class="modal fade" tabindex="-1" role="dialog" id="show_modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>@lang('main.booking')</h3>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">@lang('main.close')</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xs-12">
                                @if($room->count > 0)
                                    <form method="post" action="{{ route('hotel_mail') }}">
                                        @csrf
                                        <div id="price" class="hidden">{{ $room->price }}</div>
                                        <div id="pricec" class="hidden">{{ $room->pricec }}</div>
                                        <input type="hidden" class="form-control" name="room_id" id="room_id"
                                               value="{{ $id }}"/>
                                        <input type="hidden" class="form-control" name="hotel_id" value="{{
                                        $room->hotel_id }}"/>
                                        <div class="form-group">
                                            <label class="col-xs-4" for="title">@lang('main.name')</label>
                                            <input type="text" class="form-control" name="title" id="title" required />
                                            <span id="titleError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-xs-4" for="phone">@lang('main.phone')</label>
                                            <input type="number" class="form-control" name="phone" id="phone" required>
                                            <div id="output"></div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-xs-4" for="email">@lang('main.email')</label>
                                            <input type="email" class="form-control" name="email" id="email" required/>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-xs-4" for="content">@lang('main.message')</label>
                                            <input type="text" class="form-control" name="comment" id="comment" />
                                        </div>

                                        <div class="form-group">
                                            <label class="col-xs-4" for="count">@lang('main.count')</label>
                                            <select name="count" id="count" required>
                                                <option>@lang('main.choose')</option>
                                                @for($i=1; $i <= $room->count; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-xs-4" for="countс">@lang('main.countc')</label>
                                            <select name="countc" id="countc" required>
                                                <option value="0">@lang('main.choose')</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="">@lang('main.amount')</label>
                                            <input type="text" id="sum" name="sum" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-xs-4" for="start_d">@lang('main.start_d')</label>
                                            <input type="date" name="start_d" id="start_d" />
                                        </div>
                                        <div class="form-group">
                                            <label class="col-xs-4" for="end_d">@lang('main.end_d')</label>
                                            <input type="date" name="end_d" id="end_d" />
                                        </div>
                                        <input type="hidden" name="status" id="status" value="@lang('main.booked')">

                                        <button class="more" id="saveBtn">@lang('main.order')</button>
                                    </form>
                                @else
                                    <div class="alert alert-danger">@lang('main.empty')</div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </div>
</div>

<style>
    .fc-title{
        color: transparent;
    }
    .fc-title .busy{
        color: #fff;
    }
</style>

@endsection

