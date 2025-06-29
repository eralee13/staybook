<h2>@lang('admin.profile')</h2>
{{--<form id="send-verification" method="post" action="{{ route('verification.send') }}">--}}
{{--    @csrf--}}
{{--    <button class="more">Отправить</button>--}}
{{--</form>--}}

<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')
    @error ('name')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    <div class="form-group">
        <label for="">@lang('admin.your_name')</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}">
    </div>
    @error ('email')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    <div class="form-group">
        <label for="">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}">
        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div>
                <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                    {{ __('Your email address is unverified.') }}

                    <button form="send-verification"
                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            </div>
        @endif
    </div>
    @error ('phone')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    <div class="form-group">
        <label for="">@lang('admin.phone')</label>
        <input type="tel" name="phone" id="phone" class="phone" value="{{ old('phone', $user->phone) }}">
        <div id="output" class="output"></div>
    </div>
    @hasrole('Manager')
    <div class="form-group">
        <label for="">@lang('admin.comission') (%)</label>
        <input type="number" name="comission" value="{{ old('comission', $user->comission) }}">
    </div>
    @endhasrole
    @hasrole('B2B')
    <div class="form-group">
        <label for="">Название банка</label>
        <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}">
    </div>
    <div class="form-group">
        <label for="">ИНН</label>
        <input type="text" name="bank_inn" value="{{ old('bank_inn', $user->bank_inn) }}">
    </div>
    <div class="form-group">
        <label for="">Р/с</label>
        <input type="text" name="bank_account" value="{{ old('bank_account', $user->bank_account) }}">
    </div>
    <div class="form-group">
        <label for="">БИК</label>
        <input type="text" name="bank_bic" value="{{ old('bank_bic', $user->bank_bic) }}">
    </div>
    <div class="form-group">
        <label for="">Адрес</label>
        <input type="text" name="address" value="{{ old('address', $user->address) }}">
    </div>
    @endhasrole
    @hasrole('Hotel')
    <div class="form-group">
        <label for="">Название банка</label>
        <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}">
    </div>
    <div class="form-group">
        <label for="">ИНН</label>
        <input type="text" name="bank_inn" value="{{ old('bank_inn', $user->bank_inn) }}">
    </div>
    <div class="form-group">
        <label for="">Р/с</label>
        <input type="text" name="bank_account" value="{{ old('bank_account', $user->bank_account) }}">
    </div>
    <div class="form-group">
        <label for="">БИК</label>
        <input type="text" name="bank_bic" value="{{ old('bank_bic', $user->bank_bic) }}">
    </div>
    <div class="form-group">
        <label for="">Адрес</label>
        <input type="text" name="address" value="{{ old('address', $user->address) }}">
    </div>
    @endhasrole
    <button class="more">@lang('admin.save')</button>
    @if (session('status') === 'profile-updated')
        <p>@lang('admin.saved')</p>
    @endif
</form>
