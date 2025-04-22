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
        $latest = $element->latest;

        if( $latest )
        {
            DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $args, $element, $latest ) {

                if( $args['at'] ?? null )
                {
                    $latest->publish_at = $args['at'];
                    $latest->save();
                    return;
                }

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
