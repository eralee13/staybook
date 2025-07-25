<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #b9b9b9 !important;
        margin: 0; padding: 20px;
    }
</style>

<table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 40px auto; background-color: #fff; border-radius: 6px; overflow: hidden;">
    <tr>
        <td style="background-color: #333; color: #fff; padding: 20px;">
            <div class="logo" style="width:200px !important; height: 60px !important; display: inline-block;">
                <img src="{{ route('index')  }}/img/logo.svg" alt="Logo" style="width:
                        140px !important; height: 45px !important">
            </div>
            <h2>@lang('mail.hotel_admin.subject_edit', ['title' => $hotel->title])</h2>
        </td>
    </tr>

    <tr>
        <td style="padding: 20px;">
            <table width="100%" style="margin-bottom: 15px;">
                <tr>
                    <td>@lang('mail.hotel_admin.title', ['title' => $hotel->title])</td>
                </tr>
                <tr>
                    <td>@lang('mail.hotel_admin.edited_by', ['user' =>  \Illuminate\Support\Facades\Auth::user()->name, 'email' =>  \Illuminate\Support\Facades\Auth::user()->email])</td>
                </tr>
            </table>
        </td>
    </tr>

</table>
