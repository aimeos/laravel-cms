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

        if( $page->latest )
        {
            DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $page ) {

                $latest = $page->latest;
                $latest->published = true;
                $latest->save();

                $page->fill( (array) $latest->data );
                $page->editor = Auth::user()?->name ?? request()->ip();
                $page->save();

                $page->files()->sync( $latest->files ?? [] );
                $page->elements()->sync( $latest->elements ?? [] );

            }, 3 );

            Cache::forget( Page::key( $page ) );
        }

        return $page;
    }
}
