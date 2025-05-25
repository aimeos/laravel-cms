<?php

namespace Aimeos\Cms\GraphQL\Mutations;

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

        if( $latest = $element->latest )
        {
            if( $args['at'] ?? null )
            {
                $latest->publish_at = $args['at'];
                $latest->save();
                return $element;
            }

            $element->publish( $latest );
        }

        return $element;
    }
}
