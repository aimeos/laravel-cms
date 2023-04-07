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
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given content can be dropped by the user.
     */
    public function drop( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given content can be restored by the user.
     */
    public function keep( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given content can be purged by the user.
     */
    public function purge( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given content can be updated by the user.
     */
    public function save( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given content can be viewed by the user.
     */
    public function view( User $user ): bool
    {
        return $user->cmseditor > 0;
    }
}
