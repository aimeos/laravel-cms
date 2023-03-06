<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Aimeos\Cms\Models\Page;
use Aimeos\Cms\Models\Ref;


final class AddRef
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Ref
    {
        $ref = new Ref();
        $ref->fill( $args['input'] );
        $ref->editor = Auth::user()?->name ?? request()->ip();
        $ref->tenant_id = \Aimeos\Cms\Tenancy::value();
        $ref->save();

        return $ref;
    }
}
