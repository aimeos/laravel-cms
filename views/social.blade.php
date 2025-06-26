<meta property="og:title" content="{{ @$data->title }}" />
<meta property="og:description" content="{{ @$data->description }}" />
<meta property="og:site_name" content="{{ config('app.name') }}" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:title" content="{{ @$data->title }}" />
<meta name="twitter:description" content="{{ @$data->description }}" />

@if(($data['file'] ?? null) && ($file = $files[$data->file?->id] ?? null) && ($preview = current($file->previews ?? [])))
    <meta name="twitter:image" content="{{ cmsurl($preview) }}" />
    <meta property="og:image" content="{{ cmsurl($preview) }}" />
    <meta property="og:image:url" content="{{ cmsurl($preview) }}" />
    <meta property="og:image:width" content="{{ key($file->previews ?? []) }}" />
@endif
