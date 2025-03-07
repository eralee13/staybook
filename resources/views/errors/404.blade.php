<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>404</title>
    <link rel="stylesheet" href="{{route('index')}}/css/main.min.css">
</head>
<body>
<div class="page page-not">
    <div class="container">
        <div class="col-md-12">
            <div class="text-wrap">
                <h1>{{ __('main.error_title') }}</h1>
                <h4>@lang('main.error_not')</h4>
                <div class="btn-wrap">
                    <a href="{{ route('index') }}">@lang('main.error_back')</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page{
        padding: 150px 0;
    }
    .page-not .text-wrap{
        text-align: center;
    }
    .page-not h4{
        margin: 20px 0 40px
    }
    .page-not a{
        color: #0163b4;
        text-decoration: none;
    }
</style>
</body>
</html>