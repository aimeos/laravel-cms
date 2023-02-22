<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


if( !function_exists( 'cmsurl' ) )
{
    function cmsurl( string $path ): string
    {
        return \Illuminate\Support\Facades\Storage::disk( config( 'cms.disk', 'public' ) )->url( $path );
    }
}