<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Aimeos\Cms\Models\Element;


final class DropElement
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Element
    {
        $element = Element::withTrashed()->findOrFail( $args['id'] );
        $element->editor = Auth::user()?->name ?? request()->ip();
        $element->delete();

        return $element;
    }
}
