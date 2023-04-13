<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User;
use Aimeos\Cms\GraphQL\Exception;


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
            throw new Exception( 'Invalid credentials' );
        }

        return $guard->user();
    }
}