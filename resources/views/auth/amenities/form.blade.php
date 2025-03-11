@extends('auth.layouts.master')

@isset($amenity)
    @section('title', 'Edit' . $amenity->title)
@else
    @section('title', 'Add service')
@endisset

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <form method="post"
                          @isset($amenity)
                              action="{{ route('amenities.update', $amenity) }}"
                          @else
                              action="{{ route('amenities.store') }}"
                            @endisset>
                        @isset($amenity)
                            @method('PUT')
                        @endisset
                        <input type="hidden" name="title" value="Услуги">
                        <input type="hidden" name="hotel_id" value="{{ $hotel }}">
                        <div class="row">
                            <div class="name">Services</div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="checkbox" name="services[]" id="rent" value="Car rental"
                                    @isset($amenity)
                                        {{ in_array('Car rental', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="rent">Car rental</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="zal" type="checkbox" name="services[]" value="Concierge service"
                                    @isset($amenity)
                                        {{ in_array('Concierge service', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="zal">Concierge service</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="conf" type="checkbox" name="services[]" value="Currency exchange"
                                    @isset($amenity)
                                        {{ in_array('Currency exchange', $amenities) ? 'checked' : ''
                                                                            }}
                                            @endisset>
                                    <label for="conf">Currency exchange</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="pool" type="checkbox" name="services[]" value="Dry сleaning"
                                    @isset($amenity)
                                        {{ in_array('Dry сleaning', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="pool">Dry сleaning</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="air" type="checkbox" name="services[]" value="Ironing service"
                                    @isset($amenity)
                                        {{ in_array('Ironing service', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="air">Ironing service</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gid" type="checkbox" name="services[]" value="Laundry"
                                    @isset($amenity)
                                        {{ in_array('Laundry', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gid">Laundry</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="mas" type="checkbox" name="services[]" value="ATM"
                                    @isset($amenity)
                                        {{ in_array('ATM', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="mas">ATM</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ip" type="checkbox" name="services[]" value="Shoe shine"
                                    @isset($amenity)
                                        {{ in_array('Shoe shine', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ip">Shoe shine</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="play" type="checkbox" name="services[]" value="Bicycle rental"
                                    @isset($amenity)
                                        {{ in_array('Bicycle rental', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="play">Bicycle rental</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="safe" type="checkbox" name="services[]" value="Free bicycle rental"
                                    @isset($amenity)
                                        {{ in_array('Free bicycle rental', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="safe">Free bicycle rental</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="minibar" type="checkbox" name="services[]"
                                           value="Private check-in/check-out"
                                    @isset($amenity)
                                        {{ in_array('Private check-in/check-out', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="minibar">Private check-in/check-out</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="cond" type="checkbox" name="services[]" value="Ticket service"
                                    @isset($amenity)
                                        {{ in_array('Ticket service', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="cond">Ticket service</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="tumb" type="checkbox" name="services[]" value="Tour desk"
                                    @isset($amenity)
                                        {{ in_array('Tour desk', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="tumb">Tour desk</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="desk" type="checkbox" name="services[]" value="Trouser press"
                                    @isset($amenity)
                                        {{ in_array('Trouser press', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="desk">Trouser press</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv1" type="checkbox" name="services[]" value="Late check-out available"
                                    @isset($amenity)
                                        {{ in_array('Late check-out available', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv1">Late check-out available</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv2" type="checkbox" name="services[]" value="Early check-in"
                                    @isset($amenity)
                                        {{ in_array('Early check-in', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv2">Early check-in</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv3" type="checkbox" name="services[]" value="Express check-out"
                                    @isset($amenity)
                                        {{ in_array('Express check-out', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv3">Express check-out</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv4" type="checkbox" name="services[]" value="Express check-in"
                                    @isset($amenity)
                                        {{ in_array('Express check-in', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv4">Express check-in</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv5" type="checkbox" name="services[]" value="Tailor shop"
                                    @isset($amenity)
                                        {{ in_array('Tailor shop', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv5">Tailor shop</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv6" type="checkbox" name="services[]" value="Doorman"
                                    @isset($amenity)
                                        {{ in_array('Doorman', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv6">Doorman</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv7" type="checkbox" name="services[]" value="Security guard"
                                    @isset($amenity)
                                        {{ in_array('Security guard', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv7">Security guard</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv8" type="checkbox" name="services[]"
                                           value="Express check-in/check-out"
                                    @isset($amenity)
                                        {{ in_array('Express check-in/check-out', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv8">Express check-in/check-out</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv9" type="checkbox" name="services[]" value="Coffee/tea for guests"
                                    @isset($amenity)
                                        {{ in_array('Coffee/tea for guests', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv9">Coffee/tea for guests</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv10" type="checkbox" name="services[]" value="Iron"
                                    @isset($amenity)
                                        {{ in_array('Iron', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv10">Iron</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv11" type="checkbox" name="services[]" value="Bathrobe (on request)"
                                    @isset($amenity)
                                        {{ in_array('Bathrobe (on request)', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv11">Bathrobe (on request)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv12" type="checkbox" name="services[]" value="Luggage storage"
                                    @isset($amenity)
                                        {{ in_array('Luggage storage', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv12">Luggage storage</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>Sports and Leisure</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport1" type="checkbox" name="services[]" value="Fitness centre"
                                    @isset($amenity)
                                        {{ in_array('Fitness centre', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport1">Fitness centre</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport2" type="checkbox" name="services[]" value="Boating/Canoeing"
                                    @isset($amenity)
                                        {{ in_array('Boating/Canoeing', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport2">Boating/Canoeing</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport3" type="checkbox" name="services[]" value="Casino"
                                    @isset($amenity)
                                        {{ in_array('Casino', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport3">Casino</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport4" type="checkbox" name="services[]" value="Cycling"
                                    @isset($amenity)
                                        {{ in_array('Cycling', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport4">Cycling</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport5" type="checkbox" name="services[]" value="Darts"
                                    @isset($amenity)
                                        {{ in_array('Darts', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport5">Darts</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport6" type="checkbox" name="services[]" value="Diving"
                                    @isset($amenity)
                                        {{ in_array('Diving', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport6">Diving</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport7" type="checkbox" name="services[]" value="Fishing"
                                    @isset($amenity)
                                        {{ in_array('', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport7">Fishing</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport8" type="checkbox" name="services[]"
                                           value="Golf course (within 3 km)"
                                    @isset($amenity)
                                        {{ in_array('Golf course (within 3 km)', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport8">Golf course (within 3 km)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport9" type="checkbox" name="services[]" value="Horse riding"
                                    @isset($amenity)
                                        {{ in_array('Horse riding', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport9">Horse riding</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport10" type="checkbox" name="services[]" value="Karaoke"
                                    @isset($amenity)
                                        {{ in_array('Karaoke', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport10">Karaoke</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport11" type="checkbox" name="services[]" value="Mini golf"
                                    @isset($amenity)
                                        {{ in_array('Mini golf', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport11">Mini golf</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport12" type="checkbox" name="services[]" value="Nightclub/DJ"
                                    @isset($amenity)
                                        {{ in_array('Nightclub/DJ', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport12">Nightclub/DJ</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport13" type="checkbox" name="services[]" value="BBQ facilities"
                                    @isset($amenity)
                                        {{ in_array('BBQ facilities', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport13">BBQ facilities</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport14" type="checkbox" name="services[]" value="Billiards"
                                    @isset($amenity)
                                        {{ in_array('Billiards', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport14">Billiards</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport15" type="checkbox" name="services[]" value="Squash"
                                    @isset($amenity)
                                        {{ in_array('Squash', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport15">Squash</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport16" type="checkbox" name="services[]" value="Table tennis"
                                    @isset($amenity)
                                        {{ in_array('Table tennis', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport16">Table tennis</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport17" type="checkbox" name="services[]" value="Water sports
                                    facilities"
                                    @isset($amenity)
                                        {{ in_array('Water sports facilities', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport17">Water sports facilities</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport18" type="checkbox" name="services[]" value="Windsurfing"
                                    @isset($amenity)
                                        {{ in_array('Windsurfing', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport18">Windsurfing</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport19" type="checkbox" name="services[]" value="Entertainment"
                                    @isset($amenity)
                                        {{ in_array('Entertainment', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport19">Entertainment</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport20" type="checkbox" name="services[]" value="Hiking"
                                    @isset($amenity)
                                        {{ in_array('Hiking', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport20">Hiking</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport21" type="checkbox" name="services[]" value="Snorkelling"
                                    @isset($amenity)
                                        {{ in_array('Snorkelling', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport21">Snorkelling</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport22" type="checkbox" name="services[]" value="Tennis court"
                                    @isset($amenity)
                                        {{ in_array('Tennis court', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport22">Tennis court</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport23" type="checkbox" name="services[]" value="Library"
                                    @isset($amenity)
                                        {{ in_array('Library', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport23">Library</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport24" type="checkbox" name="services[]" value="Hunt"
                                    @isset($amenity)
                                        {{ in_array('Hunt', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport24">Hunt</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport25" type="checkbox" name="services[]" value="Rock Climbing"
                                    @isset($amenity)
                                        {{ in_array('Rock Climbing', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport25">Rock Climbing</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport26" type="checkbox" name="services[]" value="Museum"
                                    @isset($amenity)
                                        {{ in_array('Museum', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport26">Museum</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport27" type="checkbox" name="services[]" value="Barbeque"
                                    @isset($amenity)
                                        {{ in_array('Barbeque', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport27">Barbeque</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport28" type="checkbox" name="services[]" value="Picnic area"
                                    @isset($amenity)
                                        {{ in_array('Picnic area', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport28">Picnic area</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport29" type="checkbox" name="services[]" value="Barbecue grill(s)"
                                    @isset($amenity)
                                        {{ in_array('Barbecue grill(s)', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport29">Barbecue grill(s)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport30" type="checkbox" name="services[]" value="Badminton"
                                    @isset($amenity)
                                        {{ in_array('Badminton', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport30">Badminton</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport31" type="checkbox" name="services[]" value="24 - hour gym"
                                    @isset($amenity)
                                        {{ in_array('24 - hour gym', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport31">24 - hour gym</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport32" type="checkbox" name="services[]" value="Sailing"
                                    @isset($amenity)
                                        {{ in_array('Sailing', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport32">Sailing</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport33" type="checkbox" name="services[]" value="Boating"
                                    @isset($amenity)
                                        {{ in_array('Boating', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport33">Boating</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport34" type="checkbox" name="services[]" value="Golf course"
                                    @isset($amenity)
                                        {{ in_array('Golf course', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport34">Golf course</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport35" type="checkbox" name="services[]" value="Snorkelling"
                                    @isset($amenity)
                                        {{ in_array('Snorkelling', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport35">Snorkelling</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport36" type="checkbox" name="services[]" value="Gym"
                                    @isset($amenity)
                                        {{ in_array('Gym', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport36">Gym</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport37" type="checkbox" name="services[]" value="Yachting"
                                    @isset($amenity)
                                        {{ in_array('Yachting', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport37">Yachting</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport38" type="checkbox" name="services[]" value="Hiking"
                                    @isset($amenity)
                                        {{ in_array('Hiking', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport38">Hiking</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h6>General</h6>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen1" type="checkbox" name="services[]" value="Newspapers"
                                    @isset($amenity)
                                        {{ in_array('Newspapers', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen1">Newspapers</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen2" type="checkbox" name="services[]" value="Designated smoking areas"
                                    @isset($amenity)
                                        {{ in_array('Designated smoking areas', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen2">Designated smoking areas</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen3" type="checkbox" name="services[]" value="Bridal suite"
                                    @isset($amenity)
                                        {{ in_array('Bridal suite', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen3">Bridal suite</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen4" type="checkbox" name="services[]" value="Chapel/shrine"
                                    @isset($amenity)
                                        {{ in_array('Chapel/shrine', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen4">Chapel/shrine</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen5" type="checkbox" name="services[]" value="Garden"
                                    @isset($amenity)
                                        {{ in_array('Garden', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen5">Garden</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen6" type="checkbox" name="services[]" value="Baggage storage"
                                    @isset($amenity)
                                        {{ in_array('Baggage storage', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen6">Baggage storage</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen7" type="checkbox" name="services[]" value="Non-smoking rooms"
                                    @isset($amenity)
                                        {{ in_array('Non-smoking rooms', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen7">Non-smoking rooms</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen8" type="checkbox" name="services[]" value="Safe"
                                    @isset($amenity)
                                        {{ in_array('Safe', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen8">Safe</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen9" type="checkbox" name="services[]" value="Shops on site"
                                    @isset($amenity)
                                        {{ in_array('Shops on site', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen9">Shops on site</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen10" type="checkbox" name="services[]" value="Soundproof rooms"
                                    @isset($amenity)
                                        {{ in_array('Soundproof rooms', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen10">Soundproof rooms</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen11" type="checkbox" name="services[]" value="Allergy free rooms"
                                    @isset($amenity)
                                        {{ in_array('Allergy free rooms', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen11">Allergy free rooms</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen12" type="checkbox" name="services[]" value="Souvenir shop"
                                    @isset($amenity)
                                        {{ in_array('Souvenir shop', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen12">Souvenir shop</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen13" type="checkbox" name="services[]" value="Non-smoking hotel"
                                    @isset($amenity)
                                        {{ in_array('Non-smoking hotel', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen13">Non-smoking hotel</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen14" type="checkbox" name="services[]" value="Heating"
                                    @isset($amenity)
                                        {{ in_array('Heating', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen14">Heating</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen15" type="checkbox" name="services[]" value="Sunbathing terrace"
                                    @isset($amenity)
                                        {{ in_array('Sunbathing terrace', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen15">Sunbathing terrace</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen16" type="checkbox" name="services[]" value="Air conditioner"
                                    @isset($amenity)
                                        {{ in_array('Air conditioner', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen16">Air conditioner</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen17" type="checkbox" name="services[]" value="Design hotel"
                                    @isset($amenity)
                                        {{ in_array('Design hotel', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen17">Design hotel</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen18" type="checkbox" name="services[]" value="Terrace"
                                    @isset($amenity)
                                        {{ in_array('Terrace', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen18">Terrace</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen19" type="checkbox" name="services[]" value="Shared kitchen"
                                    @isset($amenity)
                                        {{ in_array('Shared kitchen', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen19">Shared kitchen</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen20" type="checkbox" name="services[]" value="Refrigerator"
                                    @isset($amenity)
                                        {{ in_array('Refrigerator', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen20">Refrigerator</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen21" type="checkbox" name="services[]" value="Washing machine"
                                    @isset($amenity)
                                        {{ in_array('Washing machine', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen21">Washing machine</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen22" type="checkbox" name="services[]" value="Ironing facilities"
                                    @isset($amenity)
                                        {{ in_array('Ironing facilities', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen22">Ironing facilities</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen23" type="checkbox" name="services[]" value="Shared fridge"
                                    @isset($amenity)
                                        {{ in_array('Shared fridge', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen23">Shared fridge</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen24" type="checkbox" name="services[]" value="Hairdryer (upon request)"
                                    @isset($amenity)
                                        {{ in_array('Hairdryer (upon request)', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen24">Hairdryer (upon request)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen25" type="checkbox" name="services[]" value="Bank"
                                    @isset($amenity)
                                        {{ in_array('Bank', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen25">Bank</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen26" type="checkbox" name="services[]" value="Lockers"
                                    @isset($amenity)
                                        {{ in_array('Lockers', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen26">Lockers</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen27" type="checkbox" name="services[]" value="Shared living room"
                                    @isset($amenity)
                                        {{ in_array('Shared living room', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen27">Shared living room</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen28" type="checkbox" name="services[]" value="Telephone"
                                    @isset($amenity)
                                        {{ in_array('Telephone', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen28">Telephone</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen29" type="checkbox" name="services[]" value="Microwave oven"
                                    @isset($amenity)
                                        {{ in_array('Microwave oven', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen29">Microwave oven</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen30" type="checkbox" name="services[]" value="Dishwasher"
                                    @isset($amenity)
                                        {{ in_array('Dishwasher', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen30">Dishwasher</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen31" type="checkbox" name="services[]" value="Conference Hall"
                                    @isset($amenity)
                                        {{ in_array('Conference Hall', $amenities) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen31">Conference Hall</label>
                                </div>
                            </div>
                        </div>
                        @csrf
                        <button class="more">Send</button>
                        <a href="{{url()->previous()}}" class="btn delete cancel">Cancel</a>
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
