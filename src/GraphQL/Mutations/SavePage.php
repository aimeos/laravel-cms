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
        $latest = $page->latest;

        $data = $args['input'] ?? [];
        $files = $args['files'] ?? [];
        $elements = $args['elements'] ?? [];
        $contents = $data['contents'] ?? null;
        unset( $data['contents'] );

        if( $data != (array) $latest?->data || $contents != (array) $latest->contents || $elements != $latest?->elements?->all() )
        {
            DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $page, $data, $contents, $elements, $files ) {

                $version = $page->versions()->create([
                    'editor' => Auth::user()?->name ?? request()->ip(),
                    'contents' => $contents,
                    'data' => $data,
                ]);

                $version->elements()->sync( $elements );
                $version->files()->sync( $files );

                // MySQL doesn't support offsets for DELETE
                $ids = Version::where( 'versionable_id', $page->id )
                    ->where( 'versionable_type', Page::class )
                    ->orderBy( 'id', 'desc' )
                    ->skip( 10 )
                    ->take( 10 )
                    ->pluck( 'id' );

                Version::whereIn( 'id', $ids )->forceDelete();
            }, 3 );
        }

        return $page;
    }
}
