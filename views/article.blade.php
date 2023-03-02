@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ asset('vendor/cms/article.css?v=1') }}">
@endPushOnce

<div class="cms-article">
	<h1>@includeIf('cms::string', ['data' => $data['title'] ?? ''])</h1>

	@includeFirst([$data['cover']['type'] ?? '', 'cms::invalid'], ['data' => $data['cover'] ?? [], 'main' => true])

	<div class="lead">@includeIf('cms::text', ['data' => $data['intro'] ?? ''])</div>

	@foreach($data['content'] ?? [] as $item)
		@includeFirst([$item['type'] ?? '', 'cms::invalid'], ['data' => $item])
	@endforeach
</div>