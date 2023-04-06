<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
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
        $page = Page::withTrashed()->findOrFail( $args['id'] );
        $page->editor = Auth::user()?->name ?? request()->ip();

        $force = $args['force'] ?? false;
        $fcn = fn() => $force ? $page->forceDelete() : $page->delete();

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( $fcn, 3 );
        Cache::forget( Page::key( $page ) );

        return $page;
    }
}
