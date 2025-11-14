@extends('layouts.master')
@section('title', 'ETG Debug')
@section('content')
    <div class="container" style="padding:2rem">
        <h2>ETG Debug</h2>
        <ul>
            <li><strong>HID:</strong> {{ $hid }}</li>
            <li><strong>Arrival:</strong> {{ $arrival }}</li>
            <li><strong>Departure:</strong> {{ $depart }}</li>
            <li><strong>Residency:</strong> {{ $residency }}</li>
        </ul>
        <h4>Rooms</h4>
        <pre>{{ json_encode($rooms, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>

        <p><a class="btn btn-primary" href="{{ url()->previous() }}">← Back</a></p>
    </div>
@endsection