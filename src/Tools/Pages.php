<?php

namespace Aimeos\Cms\Tools;

use Prism\Prism\Tool;
use Aimeos\Cms\Models\Page;


class Pages extends Tool
{
    public function __construct()
    {
        $this->as( 'search-pages' )
            ->for( 'Searches the page tree for pages matching a keyword or phrase. Returns up to 10 matching pages as JSON array of page entries.' )
            ->withStringParameter( 'term', 'Search keyword, e.g., "blog", "product", or "FAQ". One word or phrase only.')
            ->using( $this );
    }


    public function __invoke( string $term ): string
    {
        $result = Page::whereAny( ['config', 'content', 'meta', 'name', 'title'], 'like', '%' . $term . '%' )
            ->orderBy( 'id', 'desc' )
            ->take( 10 )
            ->get()
            ->map( fn( $item ) => $item->toArray() + ['url' => route('cms.page', ['path' => $item->path])] );

        return response()->json( $result );
    }
}
