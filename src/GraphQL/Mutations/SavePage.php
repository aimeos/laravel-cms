<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Page;


final class SavePage
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Page
    {
        $page = Page::findOrFail( $args['id'] );
        $key = Page::key( $page->slug, $page->lang );

        DB::transaction( fn() => $page->fill( $args['input'] ?? [] )->save(), 3 );
        Cache::forget( $key );

        return $page;
    }
}
