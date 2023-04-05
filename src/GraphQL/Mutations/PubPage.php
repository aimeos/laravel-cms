<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Page;


final class PubPage
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Page
    {
        $page = Page::findOrFail( $args['id'] );
        $page->editor = Auth::user()?->name ?? request()->ip();
        $page->data = $page->latest?->data ?: $page->data;

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( fn() => $page->save(), 3 );

        Cache::forget( Page::key( $page ) );

        return $page;
    }
}
