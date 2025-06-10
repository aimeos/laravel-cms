<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Version;
use Aimeos\Cms\Models\Element;


final class SaveElement
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Element
    {
        $element = Element::withTrashed()->findOrFail( $args['id'] );

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $element, $args ) {

            $version = $element->versions()->create( [
                'editor' => Auth::user()?->name ?? request()->ip(),
                'lang' => $args['input']['lang'] ?? null,
                'data' => $args['input'] ?? [],
            ] );

            $version->files()->sync( $args['files'] ?? [] );

            // MySQL doesn't support offsets for DELETE
            $ids = Version::where( 'versionable_id', $element->id )
                ->where( 'versionable_type', Element::class )
                ->orderBy( 'id', 'desc' )
                ->skip( 10 )
                ->take( 10 )
                ->pluck( 'id' );

            if( !$ids->isEmpty() ) {
                Version::whereIn( 'id', $ids )->forceDelete();
            }

        }, 3 );

        return $element;
    }
}
