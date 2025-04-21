<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Element;


final class KeepElement
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Element
    {
        $element = Element::withTrashed()->findOrFail( $args['id'] );
        $element->editor = Auth::user()?->email ?? request()->ip();

        $element->restore();
        return $element;
    }
}
