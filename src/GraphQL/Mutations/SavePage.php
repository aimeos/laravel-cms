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

        $elements = $args['input']['elements'] ?? [];
        $data = collect( $args['input'] )->except( ['elements', 'files'] )->all();

        if( $data != (array) $latest?->data || $elements != $latest?->elements )
        {
            DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $page, $args, $data, $elements ) {

                $version = $page->versions()->create([
                    'editor' => Auth::user()?->name ?? request()->ip(),
                    'data' => $data,
                ]);

                $version->elements()->sync( $args['input']['elements'] ?? [] );
                $version->files()->sync( $args['input']['files'] ?? [] );

                Version::where( 'versionable_id', $page->id )
                    ->where( 'versionable_type', Page::class )
                    ->orderBy( 'id', 'desc' )
                    ->skip( 10 )
                    ->take( 10 )
                    ->forceDelete();

            }, 3 );
        }

        return $page;
    }
}
