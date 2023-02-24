<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Page;


final class DropPage
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Page
    {
        $page = Page::findOrFail( $args['id'] );

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( fn() => $page->delete(), 3 );
        Cache::forget( Page::key( $page->slug, $page->lang ) );

        return $page;
    }
}
