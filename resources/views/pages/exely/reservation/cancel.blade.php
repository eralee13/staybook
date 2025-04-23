@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')


    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    @if(isset($calc->errors))
                        @foreach ($calc->errors as $error)
                            <div class="alert alert-danger">
                                <h5>{{ $error->code }}</h5>
                                <p style="margin-bottom: 0">{{ $error->message }}</p>
                            </div>
                        @endforeach
                    @else
                        <h1 data-aos="fade-up" data-aos-duration="2000">Отмена брони</h1>
                        <p>
                           Штраф за отмену составляет: {{ $calc->penaltyAmount }} {{ $request->currency }}
                        </p>
                        <form action="{{ route('res_cancel') }}">
                            <div class="form-group">
                                <label for="">Номер брони</label>
                                <input type="text" value="{{ $request->number }}" name="number">
                            </div>
                            <input type="hidden" name="amount" value="{{ $calc->penaltyAmount }}">
                            <button class="more">Отменить</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>


@endsection
