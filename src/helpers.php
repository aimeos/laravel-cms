<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


if( !function_exists( 'cmsimage' ) )
{
    function cmsimage( array $data ): string
    {
        $list = [];

        foreach( $data as $width => $path ) {
            $list[] = cmsurl( $path ) . ' ' . $width . 'w';
        }

        return implode( ',', $list );
    }
}


if( !function_exists( 'cmsurl' ) )
{
    function cmsurl( string $path ): string
    {
        if( \Illuminate\Support\Str::startsWith( $path, ['data:', 'http:', 'https:'] ) ) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::disk( config( 'cms.disk', 'public' ) )->url( $path );
    }
}


if( !function_exists( 'cmsasset' ) )
{
    function cmsasset( string $path ): string
    {
        return asset( $path ) . '?v=' . filemtime( public_path( $path ) );
    }
}