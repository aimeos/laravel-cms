@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ cmsasset('vendor/cms/hero.css') }}">
@endPushOnce

<h1 class="title">{{ @$data->title }}</h1>

@if(@$data->text)
    <div class="text">
        @markdown($data->text)
    </div>
@endif

@if(@$data->url)
    <a class="btn url" href="{{ $data->url }}">{{ @$data->button }}</a>
@endif
