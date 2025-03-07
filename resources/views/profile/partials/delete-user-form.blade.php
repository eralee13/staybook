<h2>@lang('admin.delete_account')</h2>
<form method="post" action="{{ route('profile.destroy') }}" class="p-6">
    @csrf
    @method('delete')
    <h4>@lang('admin.are_u_sure')</h4>
    <p>@lang('admin.after_delete')</p>

    <div class="form-group">
        <label for="">@lang('admin.password')</label>
        <input type="password" name="password" value="{{ __('Password') }}">
        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2"/>
    </div>
    <button class="more">@lang('admin.delete_account_btn')</button>
</form>

<style>
    h2{
        margin-top: 40px;
    }
</style>

