@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ cmsasset('vendor/cms/blog.css') }}">
@endPushOnce

@pushOnce('js')
<script defer src="{{ cmsasset('vendor/cms/blog.js') }}"></script>
@endPushOnce

@if(@$data->title)
    <h2>{{ $data->title }}</h2>
@endif
<div class="blog-items" data-blog="{{ @$data->{'parent-page'}?->value }}">
    <div class="grid">
        @foreach(@$action ?? [] as $item)
            <div class="blog-item">
                @if($article = collect(cms($item, 'content'))->first(fn($el) => @$el->type === 'article')?->data)
                    @if($file = cms($item, 'files')[@$article->file?->id] ?? null)
                        @include('cms::pic', ['file' => $file])
                    @endif
                    <h3>{{ cms($item, 'title') }}</h3>
                    <p>{{ @$article->lead }}</p>
                @else
                    <h3>{{ cms($item, 'title') }}</h3>
                @endif
                {{ __('More about') }} "<a href="{{ route('cms.page', ['path' => @$item->path]) }}">{{ cms($item, 'title') }}</a>"
            </div>
        @endforeach
    </div>
    {{ @$action?->appends(request()->query())?->links() }}
</div>
