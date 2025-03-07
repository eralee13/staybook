@extends('auth.layouts.master')

@section('title', __('admin.pages'))

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>Страницы</h1>
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
                                            <li><a class="btn view" href="{{ route('pages.show', $page)
                                            }}">Открыть</a></li>
                                            <li><a class="btn edit" href="{{ route('pages.edit', $page)
                                            }}">Редактировать</a></li>
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn delete">Удалить</button>
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
