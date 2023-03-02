@extends('cms::page')


@section('cms-head')

	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ $page->title }}</title>

	@if( in_array(app()->getLocale(), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']) )
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.rtl.min.css" rel="stylesheet" crossorigin="anonymous">
	@else
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
	@endif

	<link type="text/css" rel="stylesheet" href="{{ asset('vendor/cms/cms.css?v=1') }}">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
	<script src="{{ asset('vendor/cms/cms.js?v=1') }}" crossorigin="anonymous"></script>

	@foreach($page->data ?? [] as $item)
		@includeFirst([$item['type'] ?? '', 'cms::invalid'], ['data' => $item])
	@endforeach

@endsection


@section('cms-navbar')

<nav class="navbar navbar-expand-lg">
	<div class="container-fluid">
		<a class="navbar-brand" href="{{ $page->ancestors?->first()?->to ?: route('cms.page', ['slug' => $page->ancestors?->first()?->slug, 'lang' => $page->ancestors?->first()?->lang]) }}">
			{{ config('app.name') }}
		</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbar">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0">
			@foreach($page->nav() as $item)

				<li class="nav-item">
					<a class="nav-link {{ $item->children->count() ? 'dropdown-toggle' : '' }} {{ $page->isSelfOrDescendantOf($item) ? 'active' : '' }}"
						href="{{ $item->to ?: route('cms.page', ['slug' => $item->slug, 'lang' => $item->lang]) }}"
						@if($item->children->count()) role="button" aria-expanded="false" data-bs-toggle="dropdown" @endif
						@if($page->is($item)) aria-current="page" @endif>
						{{ $item->name }}
					</a>
					@if($item->children->count())

						<ul class="dropdown-menu">
						@foreach($item->children as $subItem)

							<li>
								<a class="dropdown-item {{ $page->isSelfOrDescendantOf($subItem) ? 'active' : '' }}"
									href="{{ $subItem->to ?: route('cms.page', ['slug' => $subItem->slug, 'lang' => $subItem->lang]) }}"
									@if($page->is($subItem)) aria-current="page" @endif>
									{{ $subItem->name }}
								</a>
							</li>
						@endforeach

						</ul>
					@endif

				</li>
			@endforeach

			</ul>
		</div>
	</div>
</nav>

@endsection


@section('cms-breadcrumb')

<nav aria-label="breadcrumb">
	<ol class="breadcrumb">
		@foreach($page->ancestors ?? [] as $item)
			@if($item->status === 1 )

				<li class="breadcrumb-item">
					<a href="{{ route('cms.page', ['slug' => $item->to ?: $item->slug]) }}">
						{{ $item->name }}
					</a>
				</li>

			@endif
		@endforeach

		<li class="breadcrumb-item active">
			{{ $page->name }}
		</li>
	</ol>
</nav>

@endsection


@section('cms-content')

<div class="container">
	@foreach($page->content as $content)
		@includeFirst([$content['data']['type'] ?? '', 'cms::invalid'], ['data' => $content['data'] ?? []])
	@endforeach
</div>

@endsection
