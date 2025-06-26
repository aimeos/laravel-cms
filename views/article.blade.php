@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ cmsasset('vendor/cms/article.css') }}">
@endPushOnce

<h1 class="title">{{ @$data->title }}</h1>

@if($file = $files[@$data->file?->id] ?? null)
	@include('cms::pic', ['file' => $file, 'main' => true, 'class' => 'cover'])
@endif

<div class="lead">
	@markdown(@$data->text)
</div>
