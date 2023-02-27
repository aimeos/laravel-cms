<div class="cms-text">
    {!! str_replace( "\n", '<br>', (new HTMLPurifier([
        'HTML.Allowed' => 'a[class|href|target],i,p,s,strong,sub,sup',
        'AutoFormat.AutoParagraph' => true,
        'AutoFormat.Linkify' => true
    ]))->purify($data['text'] ?? '') ) !!}
</div>