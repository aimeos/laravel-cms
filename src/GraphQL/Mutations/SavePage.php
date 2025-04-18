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
        $refs = $args['input']['contents'] ?? [];
        $data = collect( $args['input'] )->except( ['contents', 'files'] )->all();

        if( $data != (array) $page->latest?->data || $refs != $page->latest?->refs )
        {
            DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $page, $args, $data, $refs ) {

                $version = $page->versions()->create([
                    'editor' => Auth::user()?->name ?? request()->ip(),
                    'data' => $data,
                    'refs' => $refs
                ]);

                $version->files()->sync( $args['input']['files'] ?? [] );

                $drafts = Version::select( 'id' )
                    ->where( 'versionable_id', $page->id )
                    ->where( 'versionable_type', Page::class )
                    ->where( 'published', false )
                    ->orderBy( 'id', 'desc' )
                    ->skip( 10 )
                    ->take( 10 )
                    ->pluck( 'id' );

                $published = Version::select( 'id' )
                    ->where( 'versionable_id', $page->id )
                    ->where( 'versionable_type', Page::class )
                    ->where( 'published', true )
                    ->orderBy( 'id', 'desc' )
                    ->skip( 10 )
                    ->take( 10 )
                    ->pluck( 'id' );

                Version::whereIn( 'id', $published->merge( $drafts ) )->forceDelete();

            }, 3 );
        }

        return $page;
    }
}
