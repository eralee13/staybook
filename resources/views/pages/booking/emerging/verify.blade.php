@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')
    
    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                <h1>Подтверждение заказа</h1>
                    <table>
                        <tr>
                            <td>Отель:</td>
                            <td>{{ $hotel->title_en }}</td>
                        </tr>
                        <tr>
                            <td>Тип комнаты:</td>
                            <td>{{ $request->room_name }}</td>
                        </tr>
                        <tr>
                            <td>Тариф:</td>
                            <td>{{ $request->rate_name }}</td>
                        </tr>
                        <tr>
                            <td>Кол-во взрослых:</td>
                            <td>{{ $request->adult }}</td>
                        </tr>
                        <tr>
                            <td>Кол-во детей:</td>
                            <td>{{ $request->child }}</td>
                            {{--                                    <td>{{ implode(',', explode($order->booking->roomStays[0]->guestCount->childAges)) }}</td>--}}
                            {{--                                    <td>{{ count($order->booking->roomStays[0]->guestCount->guestCount->childAges) }}</td>--}}
                        </tr>
                        <tr>
                            <td>Кол-во номеров:</td>
                            <td>{{ $request->roomCount }}</td>
                        </tr>
                        <tr>
                            <td>Даты:</td>
                            <td>
                                @if (!empty($request->arrivalDate))
                                    {{ \Carbon\Carbon::parse($request->arrivalDate)->format('d.m.Y') }}
                                @endif
                                - 
                                @if (!empty($request->departureDate))
                                    {{ \Carbon\Carbon::parse($request->departureDate)->format('d.m.Y') }}
                                @endif
                                
                                (UTC {{ $request->utc }})
                            </td>
                        </tr>
                        <tr>
                            <td>Стоимость:</td>     
                            <td>{{ $request->sum }} {{ $request->currency ?? '$' }}</td>
                        </tr>
                        <tr>
                            <td>Правило отмены:</td>
                            <td>
                                @if($request->refundable == true)
                                    
                                        Бесплатная отмена действует до {{ \Carbon\Carbon::parse($request->cancelDate)->format('d.m.Y') }} 
                                        (UTC {{ $request->utc }})
                                        
                                        Размер штрафа: {{ $request->cancelPrice }} {{ $request->currency ?? '$' }}
                                @else
                                        Невозвратный тариф.
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>ФИО:</td>
                            <td>
                                <div class="name">{{ $request->name }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Номер телефона:</td>
                            <td>
                                <div class="name">{{ $request->phone }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Email:</td>
                            <td>
                                <div class="name">{{ $request->email }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Комментарий:</td>
                            <td>{{ $request->comment }}</td>
                        </tr>
                    </table>

                <div class="btn-wrap">
                    <form action="{{ route('book_reserve_tm') }}" method="get">
                        <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                        <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                        <input type="hidden" name="hotel_id" value="{{ $request->hotel_id }}">
                        <input type="hidden" name="room_id" value="{{ $request->room_id }}">
                        <input type="hidden" name="rate_id" value="{{ $request->rate_id }}">
                        <input type="hidden" name="meal_id" value="{{ $request->meal_id }}">
                        <input type="hidden" name="adult" value="{{ $request->adult }}">
                        <input type="hidden" name="child" value="{{ $request->child }}">
                            @if( isset($request->childAges))
                                @foreach($request->childAges as $age)
                                    <input type="hidden" name="childAges[]"
                                        value="{{ $age }}">
                                @endforeach
                            @endif
                        <input type="hidden" name="roomCount" value="{{ $request->roomCount }}">
                        <input type="hidden" name="room_name" value="{{ $request->room_name }}">
                        <input type="hidden" name="RoomTypeCode" value="{{ $request->RoomTypeCode }}">
                        <input type="hidden" name="rate_name" value="{{ $request->rate_name }}">
                        <input type="hidden" name="rate_code" value="{{ $request->rate_code }}">
                        <input type="hidden" name="refundable" value="{{ $request->refundable }}">
                        <input type="hidden" name="cancelDate" value="{{ $request->cancelDate }}">
                        <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                        <input type="hidden" name="currency"  value="{{ $request->currency }}">
                        <input type="hidden" name="utc" value="{{ $request->utc }}">
                        <input type="hidden" name="price" value="{{ $request->price }}">
                        <input type="hidden" name="sum" value="{{ $request->sum }}">
                        <input type="hidden" name="api_name" value="TM">
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="sex" value="Male">
                        <input type="hidden" name="citizenship" value="KGS">
                        <input type="hidden" name="comment" value="{{ $request->comment }}">
                        <input type="hidden" name="phone" value="{{ $request->phone }}">
                        <input type="hidden" name="email" value="{{ $request->email }}">
                        <input type="hidden" name="paxfname" value="{{ $request->paxfname }}">
                        <input type="hidden" name="paxlname" value="{{ $request->paxlname }}">
                        <input type="hidden" name="paxfname2" value="{{ $request->paxfname2 }}">
                        <input type="hidden" name="paxlname2" value="{{ $request->paxlname2 }}">
                        <input type="hidden" name="paxfname3" value="{{ $request->paxfname3 }}">
                        <input type="hidden" name="paxlname3" value="{{ $request->paxlname3 }}">
                        <input type="hidden" name="paxfname4" value="{{ $request->paxfname4 }}">
                        <input type="hidden" name="paxlname4" value="{{ $request->paxlname4 }}">
                        <button class="more">Подтвердить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
