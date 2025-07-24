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
            unset( $data['config'], $data['content'], $data['meta'] );

            $version = $page->versions()->create([
                'data' => array_map( fn( $v ) => is_null( $v ) ? (string) $v : $v, $data ),
                'editor' => Auth::user()?->name ?? request()->ip(),
                'lang' => $args['input']['lang'] ?? null,
                'aux' => [
                    'meta' => $args['input']['meta'] ?? new \stdClass(),
                    'config' => $args['input']['config'] ?? new \stdClass(),
                    'content' => $args['input']['content'] ?? [],
                ]
            ]);

            $version->elements()->attach( $args['elements'] ?? [] );
            $version->files()->attach( $args['files'] ?? [] );

            $page->removeVersions();

        }, 3 );

        return $page;
    }
}
