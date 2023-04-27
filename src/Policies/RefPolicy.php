<?php

namespace Aimeos\Cms\Policies;

use Aimeos\Cms\Models\Ref;
use App\Models\User;


class RefPolicy
{
    /**
     * Determine if the given page<->content relation can be added by the user.
     */
    public function add( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'content:add', $user );
    }


    /**
     * Determine if the given page<->content relation can be dropped by the user.
     */
    public function drop( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'content:drop', $user );
    }


    /**
     * Determine if the given page<->content relation can be moved by the user.
     */
    public function move( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'content:move', $user );
    }


    /**
     * Determine if the given page<->content relation can be published by the user.
     */
    public function publish( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'content:publish', $user );
    }


    /**
     * Determine if the given page<->content relation can be updated by the user.
     */
    public function save( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'content:save', $user );
    }
}
