<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms;


/**
 * Tenancy class for tenancy value lookups.
 */
class Tenancy
{
    /**
     * Anonymous callback which provides the value of the current tenant.
     */
    public static ?\Closure $tenantCallback = null;

    /**
     * Current tenant value.
     */
    private static ?string $value = null;


    /**
     * Returns the value for the tenant column in the models.
     *
     * @return string ID of the current tenant
     */
    public static function value() : string
    {
        if( self::$value === null && self::$tenantCallback ) {
            self::$value = self::$tenantCallback();
        }

        return (string) self::$value;
    }
}
