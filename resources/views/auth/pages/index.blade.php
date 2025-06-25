@extends('auth.layouts.master')

@section('title', __('admin.pages'))

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <div class="row align-items-center aic">
                        <div class="col-md-7">
                            <h1>Страницы</h1>
                        </div>
                        <div class="col-md-5">
                            <div class="btn-wrap">
                                <a class="btn add" href="{{ route('pages.create') }}"><i class="fa-solid
                                fa-plus"></i> @lang('admin.add')</a>
                            </div>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Код</th>
                            <th>Название</th>
                            <th>Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($pages as $page)
                            <tr>
                                <td>{{ $page->code }}</td>
                                <td>{{ $page->title }}</td>
                                <td>
                                    <form action="{{ route('pages.destroy', $page) }}" method="post">
                                        <ul>
                                            <li><a href="{{ route('pages.show', $page)
                                            }}"><img src="{{ route('index') }}/img/icons/eye.svg" alt=""></a></li>
                                            <li><a href="{{ route('pages.edit', $page)
                                            }}"><img src="{{ route('index') }}/img/icons/edit.svg" alt=""></a></li>
                                            @csrf
                                            @method('DELETE')
                                            <button><img src="{{ route('index') }}/img/icons/trash.svg" alt=""></button>
                                        </ul>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $pages->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

@endsection
