@push('css')
<link type="text/css" rel="stylesheet" href="{{ asset('vendor/cms/prism.css?v=1') }}">
@endPush

@push('js')
<script src="{{ asset('vendor/cms/prism.js?v=1') }}"></script>
@endPush

<pre><code class="language-{{ $data['language'] ?? '' }}">{{ $data['text'] ?? '' }}</code></pre>