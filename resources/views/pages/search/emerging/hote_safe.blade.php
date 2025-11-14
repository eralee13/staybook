@extends('layouts.master')

@section('title', 'Hotel (SAFE)')
@section('content')
    <div class="container py-5">
        <div class="alert alert-warning">
            {{ $message ?? 'SAFE fallback.' }}
        </div>
        <div class="small text-muted">HID: {{ $hid ?? '-' }}</div>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary mt-3">← Назад</a>
    </div>
@endsection