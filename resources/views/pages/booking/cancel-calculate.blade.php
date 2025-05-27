@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')


    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">Отмена брони</h1>
                    <p>
                        Штраф за отмену составляет: {{ $book->cancel_penalty }} {{ $request->currency }}
                    </p>
                    <form action="{{ route('cancel_confirm') }}">
                        <div class="form-group">
                            <label for="">Номер брони</label>
                            <input type="text" value="{{ $request->number }}" name="number">
                        </div>
                        <input type="hidden" name="amount" value="{{ $book->cancel_penalty }}">
                        <button class="more">Отменить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection
