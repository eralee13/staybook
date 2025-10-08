@extends('auth.layouts.master')

@isset($page)
    @section('title', 'Редактировать страницу' . $page->name)
@else
    @section('title', 'Создать страницу')
@endisset

@section('content')

    <style>
        .ck-rounded-corners .ck.ck-editor__top .ck-sticky-panel .ck-toolbar, .ck.ck-editor__top .ck-sticky-panel .ck-toolbar.ck-rounded-corners {
            border-top-left-radius: 30px;
            border-top-right-radius: 30px;
        }

        .ck.ck-editor__main > .ck-editor__editable:not(.ck-focused) {
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
        }

        .select2-container--default .select2-selection--single {
            border-radius: 30px !important;
            height: 50px !important;
            border: none !important;
        }

    </style>

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @isset($page)
                        <h1>Редактировать страницу {{ $page->title }}</h1>
                    @else
                        <h1>Создать страницу</h1>
                    @endisset
                    <form method="post" enctype="multipart/form-data"
                          @isset($page)
                              action="{{ route('pages.update', $page) }}"
                          @else
                              action="{{ route('pages.store') }}"
                            @endisset
                    >
                        @isset($page)
                            @method('PUT')
                        @endisset
                        @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="form-group">
                            <label for="">Заголовок</label>
                            <input type="text" name="title" value="{{ old('title', isset($page) ? $page->title :
                             null) }}">
                        </div>
                            @error('title_en')
                            <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <div class="form-group">
                                <label for="">Заголовок EN</label>
                                <input type="text" name="title_en" value="{{ old('title_en', isset($page) ?
                                $page->title_en : null) }}">
                            </div>
                            @include('auth.layouts.error', ['fieldname' => 'description'])
                            <div class="form-group">
                                <label for="">Описание</label>
                                <textarea name="description" id="editor" rows="3">{{ old('description', isset($page) ?
                            $page->description : null) }}</textarea>
                            </div>
                            @include('auth.layouts.error', ['fieldname' => 'description_en'])
                            <div class="form-group">
                                <label for="">Описание EN</label>
                                <textarea name="description_en" id="editor1" rows="3">{{ old('description_en', isset
                            ($page) ?
                            $page->description_en : null) }}</textarea>
                            </div>
                            <script src="https://cdn.tiny.cloud/1/yxonqgmruy7kchzsv4uizqanbapq2uta96cs0p4y91ov9iod/tinymce/6/tinymce.min.js"
                                    referrerpolicy="origin"></script>
                            <script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
                            <script>
                                ClassicEditor
                                    .create(document.querySelector('#editor'))
                                    .catch(error => {
                                        console.error(error);
                                    });
                                ClassicEditor
                                    .create(document.querySelector('#editor1'))
                                    .catch(error => {
                                        console.error(error);
                                    });
                            </script>
                            <div class="row images">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        @isset($page->image)
                                            <img src="{{ Storage::url($page->image) }}">
                                        @endisset
                                        <label for="">Изображение</label>
                                        <input type="file" name="image">
                                    </div>
                                </div>
                            </div>
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <button class="more">Отправить</button>
                            </div>
                            <div class="col-md-6">
                                <a href="{{url()->previous()}}" class="btn delete cancel">Отмена</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
