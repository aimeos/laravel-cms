<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Element;


final class AddElement
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Element
    {
        $element = new Element();

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $element, $args ) {

            $editor = Auth::user()?->name ?? request()->ip();

            $element->fill( $args['input'] ?? [] );
            $element->tenant_id = \Aimeos\Cms\Tenancy::value();
            $element->editor = $editor;
            $element->save();

            if( isset( $args['input']['data'] ) )
            {
                $version = $element->versions()->create( [
                    'data' => $args['input']['data'],
                    'published' => false,
                    'editor' => $editor
                ] );

                $version->files()->attach( $args['files'] ?? [] );
            }

        }, 3 );

        return Element::findOrFail( $element->id );
    }
}
