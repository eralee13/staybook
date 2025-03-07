<table>
    @php
        $hotel = \App\Models\Hotel::where('id', $room->hotel_id)->firstOrFail();
    @endphp
    <tr>
        <td>Room "{{ $room->title }}" in hotel "{{$hotel->title}}" updated by user
            "{{\Illuminate\Support\Facades\Auth::user()->name }}"</td>
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