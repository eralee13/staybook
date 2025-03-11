@extends('auth.layouts.master', compact('hotel'))

@section('title', __('admin.amenities'))

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <h1>@lang('admin.amenities')</h1>
                    @if(!$amenities->isEmpty())
                    <table>
                        <tr>
                            <th>@lang('admin.amenities')</th>
                            <th>@lang('admin.action')</th>
                        </tr>
                        @foreach($amenities as $amenity)
                            <tr>
                                <td>{{ $amenity->services }}</td>
                                <td><a href="{{ route('amenities.edit', $amenity) }}" class="more"><i class="fa-regular fa-pen-to-square"></i></a></td>
                            </tr>
                        @endforeach

                    </table>
                    @else
                        <div class="btn-wrap" style="margin-top: 20px">
                            <a href="{{ route('amenities.create') }}" class="more">@lang('admin.add')</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
