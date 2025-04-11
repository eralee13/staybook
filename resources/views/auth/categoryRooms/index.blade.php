@extends('auth.layouts.master')

@section('title', __('admin.plans_and_cats'))

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @include('auth.layouts.subroom')
                    <h1>@lang('admin.categoryRooms')</h1>
                    @if($cats->isNotEmpty())
                        <table class="table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('admin.title')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($cats as $cat)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $cat->__('title') }}</td>
                                    <td>
                                        <form action="{{ route('categoryRooms.destroy', $cat) }}" method="post">
                                            <ul>
                                                <li><a href="{{ route('categoryRooms.edit', $cat)
                                            }}"><img src="{{ route('index') }}/img/icons/edit.svg" alt=""></a></li>
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Do you want to delete this?');"
                                                        class="btn delete"><i class="fa-regular fa-trash"></i></button>
                                            </ul>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $cats->links('pagination::bootstrap-4') }}
                    @else
                        <div class="alert alert-danger">
                            @lang('admin.cats_not_found')
                        </div>
                    @endif
                    <div class="btn-wrap" style="margin-top: 20px">
                        <a class="btn add" href="{{ route('categoryRooms.create') }}"><i class="fa-solid
                                fa-plus"></i> @lang('admin.add')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
