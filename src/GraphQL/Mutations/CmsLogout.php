<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use App\Models\User;


final class CmsLogout
{
    /**
     * @param  null  $rootValue
     * @param  array<string, mixed>  $args
     */
    public function __invoke( $rootValue, array $args ): ?User
    {
        $guard = Auth::guard();
        $user = $guard->user();

        $guard->logout();
        return $user;
    }
}