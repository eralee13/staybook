@extends('auth.layouts.master')

@isset($contact)
    @section('title', 'Контакты')
@endisset

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <h1>Контакты</h1>
                    <form method="post" action="{{ route('contacts.update', $contact) }}">
                        @isset($contact)
                            @method('PUT')
                        @endisset
                        @error('phone')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="form-group">
                            <label for="">Номер телефона</label>
                            <input type="text" name="phone" value="{{ old('phone', isset($contact) ?
                            $contact->phone : null) }}">
                        </div>
                            @error('phone2')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <div class="form-group">
                                <label for="">Номер телефона</label>
                                <input type="text" name="phone2" value="{{ old('phone2', isset($contact) ?
                            $contact->phone2 : null) }}">
                            </div>
                        @error('email')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="form-group">
                            <label for="">Email</label>
                            <input type="email" name="email" value="{{ old('email', isset($contact) ?
                            $contact->email : null) }}">
                        </div>
                            @error('address')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        <div class="form-group">
                            <label for="">Адрес</label>
                            <input type="text" name="address" value="{{ old('address', isset($contact) ?
                                $contact->address : null) }}">
                        </div>
                            <div class="form-group">
                                <label for="">Адрес EN</label>
                                <input type="text" name="address_en" value="{{ old('address_en', isset($contact) ?
                                $contact->address_en : null) }}">
                            </div>
                        @error('whatsapp')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="form-group">
                            <label for="">Whatsapp</label>
                            <input type="text" name="whatsapp" value="{{ old('whatsapp', isset($contact) ?
                                $contact->whatsapp : null) }}">
                        </div>
                        @error('telegram')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="form-group">
                            <label for="">Telegram</label>
                            <input type="text" name="telegram" value="{{ old('telegram', isset($contact) ?
                                $contact->telegram : null) }}">
                        </div>
                        @error('instagram')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="form-group">
                            <label for="">Instagram</label>
                            <input type="text" name="instagram" value="{{ old('instagram', isset($contact) ?
                                $contact->instagram : null) }}">
                        </div>
                        @csrf
                        <button class="more">Сохранить</button>
                        <a href="{{url()->previous()}}" class="btn delete cancel">Отмена</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
