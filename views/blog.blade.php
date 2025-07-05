@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ cmsasset('vendor/cms/blog.css') }}">
@endPushOnce

@if(@$data->title)
    <h2>{{ $data->title }}</h2>
@endif
<div class="row">
    @foreach(@$action ?? [] as $item)
        <div class="col-sm-12 col-md-6 col-lg-4 blog-item">
            @if($article = collect(cms($item, 'content'))->first(fn($el) => @$el->type === 'article')?->data)
                @if($file = cms($item, 'files')[@$article->file?->id] ?? null)
                    @include('cms::pic', ['file' => $file])
                @endif
                <h3>{{ cms($item, 'title') }}</h3>
                <p>{{ @$article->lead }}</p>
            @else
                <h3>{{ cms($item, 'title') }}</h3>
            @endif
            Read "<a href="{{ route('cms.page', ['path' => @$item->path]) }}">{{ cms($item, 'title') }}</a>"
        </div>
    @endforeach

    {{ @$action?->appends(request()->query())?->links() }}
</div>
