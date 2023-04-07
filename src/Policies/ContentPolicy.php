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
        return \Aimeos\Cms\Permission::can( 'content:add', $user->cmseditor );
    }


    /**
     * Determine if the given content can be dropped by the user.
     */
    public function drop( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'content:drop', $user->cmseditor );
    }


    /**
     * Determine if the given content can be restored by the user.
     */
    public function keep( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'content:keep', $user->cmseditor );
    }


    /**
     * Determine if the given content can be moved by the user.
     */
    public function move( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'content:move', $user->cmseditor );
    }


    /**
     * Determine if the given content can be published by the user.
     */
    public function publish( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'content:publish', $user->cmseditor );
    }


    /**
     * Determine if the given content can be purged by the user.
     */
    public function purge( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'content:purge', $user->cmseditor );
    }


    /**
     * Determine if the given content can be updated by the user.
     */
    public function save( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'content:save', $user->cmseditor );
    }


    /**
     * Determine if the given content can be viewed by the user.
     */
    public function view( User $user ): bool
    {
        return \Aimeos\Cms\Permission::can( 'content:view', $user->cmseditor );
    }
}
