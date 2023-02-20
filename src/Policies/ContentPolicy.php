<?php

namespace Aimeos\Cms\Policies;

use Aimeos\Cms\Models\Content;
use App\Models\User;


class ContentPolicy
{
    /**
     * Determine if the given content can be added by the user.
     */
    public function add( User $user ): bool
    {
        return true;
    }


    /**
     * Determine if the given content can be dropped by the user.
     */
    public function drop( User $user ): bool
    {
        return true;
    }


    /**
     * Determine if the given content can be restored by the user.
     */
    public function keep( User $user ): bool
    {
        return true;
    }


    /**
     * Determine if the given content can be unpublished by the user.
     */
    public function hide( User $user ): bool
    {
        return true;
    }


    /**
     * Determine if the given content can be published by the user.
     */
    public function show( User $user ): bool
    {
        return true;
    }


    /**
     * Determine if the given content can be viewed by the user.
     */
    public function view( User $user ): bool
    {
        return true;
    }
}
