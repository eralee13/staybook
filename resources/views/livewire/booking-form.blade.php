<div class="container">
    <div class="row">
        <div class="col-8">
            <h1>Confirm</h1>
            <div class="col-12">
                {{-- <h3>{{$hotelLocal[$tmid]['title_en']}} {{$hotelLocal[$tmid]['rating']}}</h3> --}}
                <h5>Ваша поездка</h5>
            </div>
            <form action="">
                <div class="col-12">
                    <div class="form-group">
                        <label for="">@lang('main.search-adult')</label>
                        <select name="adults" id="adults" wire:model="adults">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <label for="">@lang('main.search-date')</label>
                    <input type="date" wire:model="checkin" id="start_d" name="start_d" />
                    <input type="date" wire:model="checkout" id="end_d" name="end_d" />
                </div>
    
            </form>
        </div>
        <div class="col-4">
            <div class="row">
                <div class="col-12 price-detail">
                    <div class="row">
                        <div class="col-2"><img src="" alt=""></div>
                        <div class="col-10">
                            <h6></h6>
                        </div>
                    </div>
                    <hr>
                    <h5>Детализация цены</h5>
                    <div class="row">
                        <div class="col-6"></div>
                        <div class="col-6"></div>
                    </div>
                    <div class="row">
                        <div class="col-6">Налоги (12%)</div>
                        <div class="col-6"></div>
                    </div>
                    <div class="row">
                        <div class="col-6">Total ()</div>
                        <div class="col-6"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>