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
        $element = Element::findOrFail( $args['id'] );

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $element, $args ) {

            $editor = Auth::user()?->name ?? request()->ip();

            $element->fill( $args['input'] ?? [] );
            $element->editor = $editor;
            $element->save();

            if( isset( $args['input']['data'] ) && $args['input']['data'] !== $element->latest?->data )
            {
                $version = $element->versions()->create( [
                    'data' => $args['input']['data'],
                    'published' => false,
                    'editor' => $editor
                ] );

                $version->files()->sync( $args['files'] ?? [] );

                Version::where( 'versionable_id', $element->id )
                    ->where( 'versionable_type', Element::class )
                    ->orderBy( 'id', 'desc' )
                    ->skip( 10 )
                    ->take( 10 )
                    ->forceDelete();
            }

        }, 3 );

        return $element;
    }
}
