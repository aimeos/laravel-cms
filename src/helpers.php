<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


if( !function_exists( 'cms' ) )
{
    function cms( \Aimeos\Cms\Models\Page $page, string $prop )
    {
        if( \Aimeos\Cms\Permission::can( 'page:view', auth()->user() ) ) {
            return $page->latest?->data?->{$prop} ?? $page->latest?->{$prop} ?? null;
        }

        return $page->{$prop} ?? null;
    }
}


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


if( !function_exists( 'cmsroute' ) )
{
    function cmsroute( \Aimeos\Cms\Models\Page $page ): string
    {
        if( \Aimeos\Cms\Permission::can( 'page:view', auth()->user() ) ) {
            return $page->latest?->data?->to ?: route( 'cms.page', ['path' => $page->latest?->data?->path] );
        }

        return $page->to ?: route( 'cms.page', ['path' => $page->path] );
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
        return asset( $path ) . '?v=' . ( file_exists( public_path( $path ) ) ? filemtime( public_path( $path ) ) : 0 );
    }
}
