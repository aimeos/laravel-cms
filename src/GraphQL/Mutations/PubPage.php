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
        $latest = $page->latest;

        if( $latest )
        {
            DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $args, $page, $latest ) {

                if( $args['at'] ?? null )
                {
                    $latest->publish_at = $args['at'];
                    $latest->save();
                    return;
                }

                $latest->published = true;
                $latest->save();

                $page->fill( (array) $latest->data );
                $page->contents = (array) $latest->contents;
                $page->editor = Auth::user()?->name ?? request()->ip();
                $page->save();

                $page->elements()->sync( $latest->elements ?? [] );
                $page->files()->sync( $latest->files ?? [] );

            }, 3 );

            Cache::forget( Page::key( $page ) );
        }

        return $page;
    }
}
