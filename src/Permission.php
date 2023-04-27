<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms;


/**
 * Permission class.
 */
class Permission
{
    /**
     * Action permissions
     */
    private static $can = [
        'page:view'       => 0b00000000000000000000000000000001,
        'page:save'       => 0b00000000000000000000000000000010,
        'page:add'        => 0b00000000000000000000000000000100,
        'page:drop'       => 0b00000000000000000000000000001000,
        'page:keep'       => 0b00000000000000000000000000010000,
        'page:purge'      => 0b00000000000000000000000000100000,
        'page:publish'    => 0b00000000000000000000000001000000,
        'page:move'       => 0b00000000000000000000000010000000,

        'content:view'    => 0b00000000000000000000000100000000,
        'content:save'    => 0b00000000000000000000001000000000,
        'content:add'     => 0b00000000000000000000010000000000,
        'content:drop'    => 0b00000000000000000000100000000000,
        'content:keep'    => 0b00000000000000000001000000000000,
        'content:purge'   => 0b00000000000000000010000000000000,
        'content:publish' => 0b00000000000000000100000000000000,
        'content:move'    => 0b00000000000000001000000000000000,

        'file:view'       => 0b00000000000000010000000000000000,
        'file:save'       => 0b00000000000000100000000000000000,
        'file:add'        => 0b00000000000001000000000000000000,
        'file:drop'       => 0b00000000000010000000000000000000,
        'file:keep'       => 0b00000000000100000000000000000000,
        'file:purge'      => 0b00000000001000000000000000000000,
    ];

    /**
     * Anonymous callback which allows or denies actions.
     */
    public static ?\Closure $callback = null;


    /**
     * Checks if the user has the permission for the requested action.
     *
     * @param string action Name of the requested action, e.g. "page:view"
     * @param \App\Models\User $user Laravel user object
     * @return bool TRUE of the user is allowed to perform the action, FALSE if not
     */
    public static function can( string $action, \App\Models\User $user ) : bool
    {
        if( $closure = self::$callback ) {
            return $closure( $action, $user );
        }

        return isset( self::$can[$action] ) && self::$can[$action] & $user->cmseditor;
    }
}
