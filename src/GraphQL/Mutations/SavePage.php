<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Version;
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
        $key = Page::key( $page );

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $page, $args ) {

            $editor = Auth::user()?->name ?? request()->ip();

            if( isset( $args['input']['data'] ) && $args['input']['data'] !== $page->latest?->data )
            {
                $version = $page->versions()->create( [
                    'data' => $args['input']['data'],
                    'editor' => $editor
                ] );

                $version->files()->sync( $args['input']['files'] ?? [] );

                $ids = Version::select( 'id' )
                    ->where( 'versionable_id', $page->id )
                    ->where( 'versionable_type', Page::class )
                    ->where( 'published', false )
                    ->orderBy( 'id' )
                    ->skip( 10 )
                    ->take( 100 )
                    ->pluck( 'id' );

                Version::whereIn( 'id', $ids )->forceDelete();

                unset( $args['input']['data'] );
            }

            if( isset( $args['input']['contents'] ) ) {
                $page->contents()->sync( $args['input']['contents'] );
            }

            $page->fill( $args['input'] ?? [] );
            $page->editor = $editor;
            $page->save();

        }, 3 );

        Cache::forget( $key );

        return $page;
    }
}
