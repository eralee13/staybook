<table>
    <tr>
        <td>@lang('main.name')</td>
        <td>{{ $user->name }}</td>
    </tr>
    <tr>
        <td>@lang('main.phone')</td>
        <td><a href="tel:{{ $user->phone }}">{{ $user->phone }}</a></td>
    </tr>
    <tr>
        <td>Email</td>
        <td><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
    </tr>
    <tr>
        <td>@lang('main.message')</td>
        <td>{{ $user->message }}</td>
    </tr>
</table>

<style>
    table{
        width: 100%;
    }
    table td{
        padding: 10px;
        border-top: 1px solid #ddd;
    }
</style>