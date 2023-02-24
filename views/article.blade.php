<div class="cms-blog-article">
    <h1>{{ $data['title'] ?? '' }}</h1>

    @includeIf('cms::image', ['data' => $data['image'], 'main' => true])

    <p class="intro">{{ $data['intro'] ?? '' }}</p>

    @foreach($data['content'] ?? [] as $item)
        @includeFirst([$item['type'] ?? '', 'cms::invalid'], ['data' => $item])
    @endforeach
</div>