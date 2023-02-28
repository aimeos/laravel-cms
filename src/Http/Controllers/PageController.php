<?php

namespace Aimeos\Cms\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Controller;
use Aimeos\Cms\Models\Page;


class PageController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;


    /**
     * Show the page for a given URL.
     *
     * @param string $slug Page URL segment
     * @param string $lang ISO language code
     * @return string HTML code
     */
    public function index( Request $request, string $slug, string $lang = '' ): string
    {
        if( ( $cid = $request->input( 'cid' ) ) && Gate::allowIf( fn( $user ) => $user->cmseditor > 0 ) )
        {
            $page = Page::withDepth()
                ->where( 'slug', $slug )
                ->where( 'lang', $lang )
                ->firstOrFail();

            $page->cache = -1; // don't cache sub-parts in preview requests

            return view( config( 'cms.view', 'cms::page' ), ['page' => $page] )->render();
        }

        $cache = Cache::store( config( 'cms.cache', 'file' ) );
        $key = Page::key( $slug, $lang );

        if( $html = $cache->get( $key ) ) {
            return $html;
        }

        $page = Page::withDepth()
            ->where( 'slug', $slug )
            ->where( 'lang', $lang )
            ->where( 'status', '>', 0 )
            ->firstOrFail();

        $html = view( config( 'cms.view', 'cms::page' ), ['page' => $page] )->render();

        if( $page->cache !== 0 ) {
            $cache->put( $key, $html, $page->cache ? now()->addMinutes( (int) $page->cache ) : null );
        }

        return $html;
    }
}
