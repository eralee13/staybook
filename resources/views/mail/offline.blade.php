<style>
    body {
        margin: 0;
        padding: 20px;
        font-family: Arial, sans-serif;
        background-color: #b9b9b9 !important;
    }

    .phone {
        display: inline-block;
        vertical-align: middle;
        height: 60px;
        float: right;
        padding-right: 20px;
        padding-top: 10px;
    }
</style>

<table cellpadding="0" cellspacing="0" width="100%"
       style="max-width: 650px; margin: auto; background-color: #fff; border-collapse: collapse; box-shadow: 0 0 10px rgba(0,0,0,0.05);">

    <!-- Header block -->
    <tr>
        <td style="background-color: #4a4a4a; color: #fff; padding: 20px;">
            <div class="logo" style="width:200px !important; height: 60px !important; display: inline-block;">
                <img src="{{ route('index')  }}/img/logo.svg" alt="Logo" style="width:
                        140px !important; height: 45px !important">
            </div>
            <div class="phone"><a href="tel:+996 227 225 227">+996 227 225 227</a></div>
        </td>
    </tr>

    <!-- Guest & Dates -->
    <tr>
        <td style="padding: 20px; background-color: #f8f8f8;">
            <table width="100%" style="font-size: 14px;">
                <tr>
                    <td><strong>Checkin</strong></td>
                    <td>{{ $offline->arrivalDate ?? '' }} - {{ $offline->departureDate ?? '' }}</td>
                </tr>
                <tr>
                    <td><strong>City</strong></td>
                    <td>{{ $offline->city }}</td>
                </tr>
                <tr>
                    <td style="padding-top: 15px;"><strong>Hotel</strong></td>
                    <td style="padding-top: 15px;" align="right">{{ $offline->type }}, Rating: {{ $offline->rating }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td style="padding: 15px 20px;">
            <h3 style="margin: 0; font-size: 15px; color: #333;">Rooms</h3>
            <p style="margin: 5px 0 0; font-size: 13px; color: #666;">Type room: {{ $offline->type_room }} <br>
                Number of rooms {{ $offline->room_count ?? ''}} <br>
                from {{ $offline->min_price }} to {{ $offline->max_price }} {{ $offline->currency }}</p>
        </td>
    </tr>

    <!-- Divider -->
    <tr>
        <td>
            <hr style="border: none; border-top: 1px solid #ddd; margin: 0;">
        </td>
    </tr>

    <!-- Booking details -->
    <tr>
        <td style="padding: 20px;">
            <table width="100%" style="font-size: 14px;" cellpadding="6">
                <tr>
                    <td><strong>Offline №</strong></td>
                    <td align="right">{{ $offline->id ?? ''}}</td>
                </tr>
                <tr>
                    <td><strong>Offline made on</strong></td>
                    <td align="right">{{ $offline->created_at }}</td>
                </tr>
                <tr>
                    <td><strong>Meal</strong></td>
                    <td align="right">{{ $offline->meal ?? ''}}</td>
                </tr>
                <tr>
                    <td><strong>Accommodation</strong></td>
                    <td align="right">{{ $offline->accommodation ?? '' }}</td>
                </tr>
                <tr>
                    <td><strong>Count</strong></td>
                    <td align="right">Adult: {{ $offline->adult }}, Child: {{ $offline->child }} ({{ $offline->childAges }})</td>
                </tr>
                <tr>
                    <td><strong>Name:</strong></td>
                    <td align="right">{{ $offline->name}}</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td align="right" style="color: #000; font-size: 16px;">{{ $offline->email}}</td>
                </tr>
                <tr>
                    <td><strong>Message:</strong></td>
                    <td align="right" style="color: #000; font-size: 16px;">{{ $offline->message}}</td>
                </tr>
            </table>

        </td>
    </tr>
    <tr>
        <td style="background-color: #0061ae; color: #fff; text-align: center; padding: 15px;">
            {{-- <p style="margin: 0;">Нужна помощь? <strong>Отдел поддержки отелей</strong></p> --}} <br>
        </td>
    </tr>
</table>

