<?php

namespace Aimeos\Cms\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Routing\Controller;
use Aimeos\Cms\Models\Page;


class PageController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * Show the page for a given URL.
     *
     * @param string $slug Page URL segment
     * @param string $lang ISO language code
     * @return string HTML code
     */
    public function index( string $slug, string $lang = '' ): string
    {
        $cache = Cache::store( config( 'cms.cache', 'file' ) );
        $key = Page::key( $slug, $lang );

        if( $html = $cache->get( $key ) ) {
            return $html;
        }

        $page = Page::where( 'slug', $slug )->where( 'lang', $lang )->where( 'status', '>', 0 )->firstOrFail();
        $html = view( config( 'cms.view', 'cms::page' ), ['page' => $page] )->render();

        if( $page->cache !== 0 ) {
            $cache->put( $key, $html, $page->cache ? now()->addMinutes( (int) $page->cache ) : null );
        }

        return $html;
    }
}
