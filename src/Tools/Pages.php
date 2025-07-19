<?php

namespace Aimeos\Cms\Tools;

use Prism\Prism\Tool;
use Aimeos\Cms\Models\Page;


class Pages extends Tool
{
    public function __construct()
    {
        $this->as( 'pages' )
            ->for( 'Search for pages in the page tree' )
            ->withStringParameter( 'any', 'Single search term, use one topic at a time.')
            ->using( $this );
    }


    public function __invoke( string $any ): string
    {
        $items = Page::whereAny( ['config', 'content', 'meta', 'name', 'title'], 'like', '%' . $any . '%' )
            ->orderBy( 'id', 'desc' )
            ->take( 10 )
            ->get();

        return view( 'prompts.pages', ['results' => $items] )->render();
    }
}
