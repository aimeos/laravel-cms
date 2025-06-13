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
        $page = Page::withTrashed()->findOrFail( $args['id'] );

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $page, $args ) {

            $data = $args['input'] ?? [];
            unset( $data['contents'] );

            $version = $page->versions()->create([
                'editor' => Auth::user()?->name ?? request()->ip(),
                'contents' => $args['input']['contents'] ?? null,
                'lang' => $args['input']['lang'] ?? null,
                'data' => $data,
            ]);

            $version->elements()->sync( $args['elements'] ?? [] );
            $version->files()->sync( $args['files'] ?? [] );

            $page->removeVersions();

        }, 3 );

        return $page;
    }
}
