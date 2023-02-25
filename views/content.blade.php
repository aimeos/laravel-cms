@extends('cms::page')

@section('content')
    @foreach($page->content->data ?? [] as $item)
        @includeFirst([$item['type'] ?? '', 'cms::invalid'], ['data' => $item])
    @endforeach
@endsection
