@extends('auth.layouts.master')

@isset($service)
    @section('title', 'Edit' . $service->title)
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
                          @isset($service)
                              action="{{ route('services.update', $service) }}"
                          @else
                              action="{{ route('services.store') }}"
                            @endisset>
                        @isset($service)
                            @method('PUT')
                        @endisset
                        <input type="hidden" name="title" value="Услуги">
                        <input type="hidden" name="hotel_id" value="{{ $hotel }}">
                        <div class="row">
                            <div class="name">Services</div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="checkbox" name="services[]" id="rent" value="Car rental"
                                    @isset($service)
                                        {{ in_array('Car rental', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="rent">Car rental</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="zal" type="checkbox" name="services[]" value="Concierge service"
                                    @isset($service)
                                        {{ in_array('Concierge service', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="zal">Concierge service</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="conf" type="checkbox" name="services[]" value="Currency exchange"
                                    @isset($service)
                                        {{ in_array('Currency exchange', $services) ? 'checked' : ''
                                                                            }}
                                            @endisset>
                                    <label for="conf">Currency exchange</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="pool" type="checkbox" name="services[]" value="Dry сleaning"
                                    @isset($service)
                                        {{ in_array('Dry сleaning', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="pool">Dry сleaning</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="air" type="checkbox" name="services[]" value="Ironing service"
                                    @isset($service)
                                        {{ in_array('Ironing service', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="air">Ironing service</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gid" type="checkbox" name="services[]" value="Laundry"
                                    @isset($service)
                                        {{ in_array('Laundry', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gid">Laundry</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="mas" type="checkbox" name="services[]" value="ATM"
                                    @isset($service)
                                        {{ in_array('ATM', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="mas">ATM</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="ip" type="checkbox" name="services[]" value="Shoe shine"
                                    @isset($service)
                                        {{ in_array('Shoe shine', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="ip">Shoe shine</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="play" type="checkbox" name="services[]" value="Bicycle rental"
                                    @isset($service)
                                        {{ in_array('Bicycle rental', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="play">Bicycle rental</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="safe" type="checkbox" name="services[]" value="Free bicycle rental"
                                    @isset($service)
                                        {{ in_array('Free bicycle rental', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="safe">Free bicycle rental</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="minibar" type="checkbox" name="services[]"
                                           value="Private check-in/check-out"
                                    @isset($service)
                                        {{ in_array('Private check-in/check-out', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="minibar">Private check-in/check-out</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="cond" type="checkbox" name="services[]" value="Ticket service"
                                    @isset($service)
                                        {{ in_array('Ticket service', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="cond">Ticket service</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="tumb" type="checkbox" name="services[]" value="Tour desk"
                                    @isset($service)
                                        {{ in_array('Tour desk', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="tumb">Tour desk</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="desk" type="checkbox" name="services[]" value="Trouser press"
                                    @isset($service)
                                        {{ in_array('Trouser press', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="desk">Trouser press</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv1" type="checkbox" name="services[]" value="Late check-out available"
                                    @isset($service)
                                        {{ in_array('Late check-out available', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv1">Late check-out available</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv2" type="checkbox" name="services[]" value="Early check-in"
                                    @isset($service)
                                        {{ in_array('Early check-in', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv2">Early check-in</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv3" type="checkbox" name="services[]" value="Express check-out"
                                    @isset($service)
                                        {{ in_array('Express check-out', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv3">Express check-out</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv4" type="checkbox" name="services[]" value="Express check-in"
                                    @isset($service)
                                        {{ in_array('Express check-in', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv4">Express check-in</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv5" type="checkbox" name="services[]" value="Tailor shop"
                                    @isset($service)
                                        {{ in_array('Tailor shop', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv5">Tailor shop</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv6" type="checkbox" name="services[]" value="Doorman"
                                    @isset($service)
                                        {{ in_array('Doorman', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv6">Doorman</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv7" type="checkbox" name="services[]" value="Security guard"
                                    @isset($service)
                                        {{ in_array('Security guard', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv7">Security guard</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv8" type="checkbox" name="services[]"
                                           value="Express check-in/check-out"
                                    @isset($service)
                                        {{ in_array('Express check-in/check-out', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv8">Express check-in/check-out</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv9" type="checkbox" name="services[]" value="Coffee/tea for guests"
                                    @isset($service)
                                        {{ in_array('Coffee/tea for guests', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv9">Coffee/tea for guests</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv10" type="checkbox" name="services[]" value="Iron"
                                    @isset($service)
                                        {{ in_array('Iron', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv10">Iron</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv11" type="checkbox" name="services[]" value="Bathrobe (on request)"
                                    @isset($service)
                                        {{ in_array('Bathrobe (on request)', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="serv11">Bathrobe (on request)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="serv12" type="checkbox" name="services[]" value="Luggage storage"
                                    @isset($service)
                                        {{ in_array('Luggage storage', $services) ? 'checked' : '' }}
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
                                    @isset($service)
                                        {{ in_array('Fitness centre', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport1">Fitness centre</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport2" type="checkbox" name="services[]" value="Boating/Canoeing"
                                    @isset($service)
                                        {{ in_array('Boating/Canoeing', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport2">Boating/Canoeing</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport3" type="checkbox" name="services[]" value="Casino"
                                    @isset($service)
                                        {{ in_array('Casino', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport3">Casino</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport4" type="checkbox" name="services[]" value="Cycling"
                                    @isset($service)
                                        {{ in_array('Cycling', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport4">Cycling</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport5" type="checkbox" name="services[]" value="Darts"
                                    @isset($service)
                                        {{ in_array('Darts', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport5">Darts</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport6" type="checkbox" name="services[]" value="Diving"
                                    @isset($service)
                                        {{ in_array('Diving', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport6">Diving</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport7" type="checkbox" name="services[]" value="Fishing"
                                    @isset($service)
                                        {{ in_array('', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport7">Fishing</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport8" type="checkbox" name="services[]"
                                           value="Golf course (within 3 km)"
                                    @isset($service)
                                        {{ in_array('Golf course (within 3 km)', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport8">Golf course (within 3 km)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport9" type="checkbox" name="services[]" value="Horse riding"
                                    @isset($service)
                                        {{ in_array('Horse riding', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport9">Horse riding</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport10" type="checkbox" name="services[]" value="Karaoke"
                                    @isset($service)
                                        {{ in_array('Karaoke', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport10">Karaoke</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport11" type="checkbox" name="services[]" value="Mini golf"
                                    @isset($service)
                                        {{ in_array('Mini golf', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport11">Mini golf</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport12" type="checkbox" name="services[]" value="Nightclub/DJ"
                                    @isset($service)
                                        {{ in_array('Nightclub/DJ', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport12">Nightclub/DJ</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport13" type="checkbox" name="services[]" value="BBQ facilities"
                                    @isset($service)
                                        {{ in_array('BBQ facilities', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport13">BBQ facilities</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport14" type="checkbox" name="services[]" value="Billiards"
                                    @isset($service)
                                        {{ in_array('Billiards', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport14">Billiards</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport15" type="checkbox" name="services[]" value="Squash"
                                    @isset($service)
                                        {{ in_array('Squash', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport15">Squash</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport16" type="checkbox" name="services[]" value="Table tennis"
                                    @isset($service)
                                        {{ in_array('Table tennis', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport16">Table tennis</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport17" type="checkbox" name="services[]" value="Water sports
                                    facilities"
                                    @isset($service)
                                        {{ in_array('Water sports facilities', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport17">Water sports facilities</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport18" type="checkbox" name="services[]" value="Windsurfing"
                                    @isset($service)
                                        {{ in_array('Windsurfing', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport18">Windsurfing</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport19" type="checkbox" name="services[]" value="Entertainment"
                                    @isset($service)
                                        {{ in_array('Entertainment', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport19">Entertainment</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport20" type="checkbox" name="services[]" value="Hiking"
                                    @isset($service)
                                        {{ in_array('Hiking', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport20">Hiking</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport21" type="checkbox" name="services[]" value="Snorkelling"
                                    @isset($service)
                                        {{ in_array('Snorkelling', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport21">Snorkelling</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport22" type="checkbox" name="services[]" value="Tennis court"
                                    @isset($service)
                                        {{ in_array('Tennis court', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport22">Tennis court</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport23" type="checkbox" name="services[]" value="Library"
                                    @isset($service)
                                        {{ in_array('Library', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport23">Library</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport24" type="checkbox" name="services[]" value="Hunt"
                                    @isset($service)
                                        {{ in_array('Hunt', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport24">Hunt</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport25" type="checkbox" name="services[]" value="Rock Climbing"
                                    @isset($service)
                                        {{ in_array('Rock Climbing', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport25">Rock Climbing</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport26" type="checkbox" name="services[]" value="Museum"
                                    @isset($service)
                                        {{ in_array('Museum', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport26">Museum</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport27" type="checkbox" name="services[]" value="Barbeque"
                                    @isset($service)
                                        {{ in_array('Barbeque', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport27">Barbeque</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport28" type="checkbox" name="services[]" value="Picnic area"
                                    @isset($service)
                                        {{ in_array('Picnic area', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport28">Picnic area</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport29" type="checkbox" name="services[]" value="Barbecue grill(s)"
                                    @isset($service)
                                        {{ in_array('Barbecue grill(s)', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport29">Barbecue grill(s)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport30" type="checkbox" name="services[]" value="Badminton"
                                    @isset($service)
                                        {{ in_array('Badminton', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport30">Badminton</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport31" type="checkbox" name="services[]" value="24 - hour gym"
                                    @isset($service)
                                        {{ in_array('24 - hour gym', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport31">24 - hour gym</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport32" type="checkbox" name="services[]" value="Sailing"
                                    @isset($service)
                                        {{ in_array('Sailing', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport32">Sailing</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport33" type="checkbox" name="services[]" value="Boating"
                                    @isset($service)
                                        {{ in_array('Boating', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport33">Boating</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport34" type="checkbox" name="services[]" value="Golf course"
                                    @isset($service)
                                        {{ in_array('Golf course', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport34">Golf course</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport35" type="checkbox" name="services[]" value="Snorkelling"
                                    @isset($service)
                                        {{ in_array('Snorkelling', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport35">Snorkelling</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport36" type="checkbox" name="services[]" value="Gym"
                                    @isset($service)
                                        {{ in_array('Gym', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport36">Gym</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport37" type="checkbox" name="services[]" value="Yachting"
                                    @isset($service)
                                        {{ in_array('Yachting', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="sport37">Yachting</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="sport38" type="checkbox" name="services[]" value="Hiking"
                                    @isset($service)
                                        {{ in_array('Hiking', $services) ? 'checked' : '' }}
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
                                    @isset($service)
                                        {{ in_array('Newspapers', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen1">Newspapers</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen2" type="checkbox" name="services[]" value="Designated smoking areas"
                                    @isset($service)
                                        {{ in_array('Designated smoking areas', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen2">Designated smoking areas</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen3" type="checkbox" name="services[]" value="Bridal suite"
                                    @isset($service)
                                        {{ in_array('Bridal suite', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen3">Bridal suite</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen4" type="checkbox" name="services[]" value="Chapel/shrine"
                                    @isset($service)
                                        {{ in_array('Chapel/shrine', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen4">Chapel/shrine</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen5" type="checkbox" name="services[]" value="Garden"
                                    @isset($service)
                                        {{ in_array('Garden', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen5">Garden</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen6" type="checkbox" name="services[]" value="Baggage storage"
                                    @isset($service)
                                        {{ in_array('Baggage storage', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen6">Baggage storage</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen7" type="checkbox" name="services[]" value="Non-smoking rooms"
                                    @isset($service)
                                        {{ in_array('Non-smoking rooms', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen7">Non-smoking rooms</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen8" type="checkbox" name="services[]" value="Safe"
                                    @isset($service)
                                        {{ in_array('Safe', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen8">Safe</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen9" type="checkbox" name="services[]" value="Shops on site"
                                    @isset($service)
                                        {{ in_array('Shops on site', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen9">Shops on site</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen10" type="checkbox" name="services[]" value="Soundproof rooms"
                                    @isset($service)
                                        {{ in_array('Soundproof rooms', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen10">Soundproof rooms</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen11" type="checkbox" name="services[]" value="Allergy free rooms"
                                    @isset($service)
                                        {{ in_array('Allergy free rooms', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen11">Allergy free rooms</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen12" type="checkbox" name="services[]" value="Souvenir shop"
                                    @isset($service)
                                        {{ in_array('Souvenir shop', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen12">Souvenir shop</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen13" type="checkbox" name="services[]" value="Non-smoking hotel"
                                    @isset($service)
                                        {{ in_array('Non-smoking hotel', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen13">Non-smoking hotel</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen14" type="checkbox" name="services[]" value="Heating"
                                    @isset($service)
                                        {{ in_array('Heating', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen14">Heating</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen15" type="checkbox" name="services[]" value="Sunbathing terrace"
                                    @isset($service)
                                        {{ in_array('Sunbathing terrace', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen15">Sunbathing terrace</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen16" type="checkbox" name="services[]" value="Air conditioner"
                                    @isset($service)
                                        {{ in_array('Air conditioner', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen16">Air conditioner</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen17" type="checkbox" name="services[]" value="Design hotel"
                                    @isset($service)
                                        {{ in_array('Design hotel', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen17">Design hotel</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen18" type="checkbox" name="services[]" value="Terrace"
                                    @isset($service)
                                        {{ in_array('Terrace', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen18">Terrace</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen19" type="checkbox" name="services[]" value="Shared kitchen"
                                    @isset($service)
                                        {{ in_array('Shared kitchen', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen19">Shared kitchen</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen20" type="checkbox" name="services[]" value="Refrigerator"
                                    @isset($service)
                                        {{ in_array('Refrigerator', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen20">Refrigerator</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen21" type="checkbox" name="services[]" value="Washing machine"
                                    @isset($service)
                                        {{ in_array('Washing machine', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen21">Washing machine</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen22" type="checkbox" name="services[]" value="Ironing facilities"
                                    @isset($service)
                                        {{ in_array('Ironing facilities', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen22">Ironing facilities</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen23" type="checkbox" name="services[]" value="Shared fridge"
                                    @isset($service)
                                        {{ in_array('Shared fridge', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen23">Shared fridge</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen24" type="checkbox" name="services[]" value="Hairdryer (upon request)"
                                    @isset($service)
                                        {{ in_array('Hairdryer (upon request)', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen24">Hairdryer (upon request)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen25" type="checkbox" name="services[]" value="Bank"
                                    @isset($service)
                                        {{ in_array('Bank', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen25">Bank</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen26" type="checkbox" name="services[]" value="Lockers"
                                    @isset($service)
                                        {{ in_array('Lockers', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen26">Lockers</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen27" type="checkbox" name="services[]" value="Shared living room"
                                    @isset($service)
                                        {{ in_array('Shared living room', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen27">Shared living room</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen28" type="checkbox" name="services[]" value="Telephone"
                                    @isset($service)
                                        {{ in_array('Telephone', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen28">Telephone</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen29" type="checkbox" name="services[]" value="Microwave oven"
                                    @isset($service)
                                        {{ in_array('Microwave oven', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen29">Microwave oven</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen30" type="checkbox" name="services[]" value="Dishwasher"
                                    @isset($service)
                                        {{ in_array('Dishwasher', $services) ? 'checked' : '' }}
                                            @endisset>
                                    <label for="gen30">Dishwasher</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input id="gen31" type="checkbox" name="services[]" value="Conference Hall"
                                    @isset($service)
                                        {{ in_array('Conference Hall', $services) ? 'checked' : '' }}
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
