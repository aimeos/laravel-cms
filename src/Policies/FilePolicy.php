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
        return \Aimeos\Cms\Permission::can( 'file:add', $user );
    }


    /**
     * Determine if the given file can be dropped by the user.
     */
    public function drop( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'file:drop', $user );
    }


    /**
     * Determine if the given file can be restored by the user.
     */
    public function keep( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'file:keep', $user );
    }


    /**
     * Determine if the given file can be purged by the user.
     */
    public function purge( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'file:purge', $user );
    }


    /**
     * Determine if the given file can be updated by the user.
     */
    public function save( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'file:save', $user );
    }


    /**
     * Determine if the given file can be viewed by the user.
     */
    public function view( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'file:view', $user );
    }
}