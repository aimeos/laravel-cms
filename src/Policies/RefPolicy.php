<?php

namespace Aimeos\Cms\Policies;

use Aimeos\Cms\Models\Ref;
use App\Models\User;


class RefPolicy
{
    /**
     * Determine if the given page<->content can be added by the user.
     */
    public function add( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given page<->content can be dropped by the user.
     */
    public function drop( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given page<->content can be updated by the user.
     */
    public function save( User $user ): bool
    {
        return $user->cmseditor > 0;
    }
}
