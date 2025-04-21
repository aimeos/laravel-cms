<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Element;


final class PubElement
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Element
    {
        $element = Element::findOrFail( $args['id'] );

        if( $element->latest )
        {
            DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $element ) {

                $latest = $element->latest;
                $latest->published = true;
                $latest->save();

                $element->data = $latest->data;
                $element->editor = Auth::user()?->name ?? request()->ip();
                $element->save();

                $element->files()->sync( $latest->files ?? [] );

            }, 3 );
        }

        return $element;
    }
}
