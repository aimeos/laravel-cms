<ul>
    @foreach($results as $page)
        <li><a href="{{ route('cms.page', ['path' => $page->path]) }}">{{ $page->title }}</a></li>
    @endforeach
</ul>
