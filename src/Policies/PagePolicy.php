<?php

namespace Aimeos\Cms\Policies;

use Aimeos\Cms\Models\Page;
use App\Models\User;


class PagePolicy
{
    /**
     * Determine if the given page can be added by the user.
     */
    public function add( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given page can be dropped by the user.
     */
    public function drop( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given page can be moved by the user.
     */
    public function move( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given page can be saved by the user.
     */
    public function save( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given page can be viewed by the user.
     */
    public function view( User $user ): bool
    {
        return $user->cmseditor > 0;
    }
}