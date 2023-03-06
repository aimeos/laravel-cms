<?php

namespace Aimeos\Cms\Policies;

use Aimeos\Cms\Models\File;
use App\Models\User;


class FilePolicy
{
    /**
     * Determine if the given file can be added by the user.
     */
    public function add( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given file can be dropped by the user.
     */
    public function drop( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given file can be restored by the user.
     */
    public function keep( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given file can be updated by the user.
     */
    public function save( User $user ): bool
    {
        return $user->cmseditor > 0;
    }


    /**
     * Determine if the given file can be viewed by the user.
     */
    public function view( User $user ): bool
    {
        return $user->cmseditor > 0;
    }
}