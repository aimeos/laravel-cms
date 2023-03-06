<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User;


final class CmsLogin
{
    /**
     * @param  null  $rootValue
     * @param  array<string, mixed>  $args
     */
    public function __invoke( $rootValue, array $args ): User
    {
        $guard = Auth::guard();

        if( !$guard->attempt( $args ) ) {
            throw new \RuntimeException( 'Invalid credentials' );
        }

        return $guard->user();
    }
}