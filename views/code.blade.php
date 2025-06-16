@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ cmsasset('vendor/cms/prism.css') }}">
@endPushOnce

@pushOnce('js')
<script defer src="{{ cmsasset('vendor/cms/prism.js') }}"></script>
@endPushOnce

<pre data-cid="{{ $cid ?? '' }}">
<code class="language-{{ $data['lang']['value']  ?? '' }}">{{ $data['text'] ?? '' }}</code>
</pre>
