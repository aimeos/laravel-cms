<!doctype html>
<html lang="en">
<head>
    <title>{{ $page->title }}</title>
    @foreach($page->data as $item)
        @include($item['type'], ['data' => $item])
    @endforeach
</head>
<body>
<div class="mx-auto max-w-2xl">
    <h1>Slug: {{ $page->slug }}</h1>
</div>
<div class="mx-auto max-w-2xl">
    @foreach($page->content->data as $item)
        @include($item['type'] ?? 'cms::text', ['data' => $item])
    @endforeach
</div>
</body>
</html>
