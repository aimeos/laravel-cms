@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ asset('vendor/cms/article.css?v=1') }}">
@endPushOnce

<div class="cms-article">
    <h1>{{ $data['title'] ?? '' }}</h1>

    @includeIf('cms::image', ['data' => $data['image'], 'main' => true])

    <p class="lead">{{ $data['intro'] ?? '' }}</p>

    @foreach($data['content'] ?? [] as $item)
        @includeFirst([$item['type'] ?? '', 'cms::invalid'], ['data' => $item])
    @endforeach
</div>