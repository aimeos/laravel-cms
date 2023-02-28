<span class="cms-string">{!! (new HTMLPurifier([
    'HTML.Allowed' => 'a[href|target],i,s,strong,sub,sup',
    'AutoFormat.Linkify' => true
]))->purify($data['text'] ?? '') ) !!}</span>