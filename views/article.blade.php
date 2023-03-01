@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ asset('vendor/cms/article.css?v=1') }}">
@endPushOnce

<div class="cms-article">
	<h1>@includeFirst([$data['title']['type'] ?? '', 'cms::invalid'], ['data' => $data['title'] ?? []])</h1>

	@includeFirst([$data['image']['type'] ?? '', 'cms::invalid'], ['data' => $data['image'] ?? [], 'main' => true])

	<div class="lead">@includeFirst([$data['intro']['type'] ?? '', 'cms::invalid'], ['data' => $data['intro'] ?? []])</div>

	@foreach($data['content'] ?? [] as $item)
		@includeFirst([$item['type'] ?? '', 'cms::invalid'], ['data' => $item])
	@endforeach
</div>