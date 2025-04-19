@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ asset('vendor/cms/prism.css') }}">
@endPushOnce

@pushOnce('js')
<script defer src="{{ asset('vendor/cms/prism.js') }}"></script>
@endPushOnce

<pre><code class="language-{{ $lang ?? '' }}">
{{ $text ?? '' }}
</code></pre>