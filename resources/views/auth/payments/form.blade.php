@extends('auth.layouts.master')

@isset($payment)
    @section('title', 'Edit')
@else
    @section('title', 'Add payment')
@endisset

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <h1>Payment Methods</h1>
                    <form method="post"
                        @isset($payment)
                            action="{{ route('payments.update', $payment) }}"
                        @else
                            action="{{ route('payments.store') }}"
                        @endisset>
                        @isset($payment)
                            @method('PUT')
                        @endisset
                        <input type="hidden" name="hotel_id" value="{{ $hotel }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="checkbox" name="payments[]" id="visa" value="VISA"
                                    @isset($payment){{in_array('VISA', $payments) ? 'checked' : '' }}@endisset>
                                    <label for="visa">VISA</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="zal" type="checkbox" name="payments[]" value="MasterCard"
                                    @isset($payment){{in_array('MasterCard', $payments) ? 'checked' : '' }}@endisset>
                                    <label for="zal">MasterCard</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="conf" type="checkbox" name="payments[]" value="Elcat"
                                    @isset($payment){{in_array('Elcat', $payments) ? 'checked' : '' }}@endisset>
                                    <label for="conf">Elcat</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="pool" type="checkbox" name="payments[]" value="PayPal"
                                    @isset($payment){{in_array('PayPal', $payments) ? 'checked' : '' }}@endisset>
                                    <label for="pool">PayPal</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="bank" type="checkbox" name="payments[]" value="Bank Transfer"
                                    @isset($payment){{in_array('Bank Transfer', $payments) ? 'checked' : '' }}@endisset>
                                    <label for="bank">Bank Transfer</label>
                                </div>
                            </div>
                        </div>
                        @csrf
                        <button class="more">Отправить</button>
                        <a href="{{url()->previous()}}" class="btn delete cancel">Отмена</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .admin label {
            display: inline-block;
        }
    </style>

@endsection
