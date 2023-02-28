<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
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
        $page->editor = Auth::user()?->name ?? request()->ip();
        $key = Page::key( $page->slug, $page->lang );

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( fn() => $page->fill( $args['input'] ?? [] )->save(), 3 );
        Cache::forget( $key );

        return $page;
    }
}
