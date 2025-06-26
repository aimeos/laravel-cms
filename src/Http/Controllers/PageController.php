<?php

namespace Aimeos\Cms\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Controller;
use Aimeos\Cms\Models\Version;
use Aimeos\Cms\Models\Page;
use Aimeos\Cms\Permission;


class PageController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Return the admin page view.
     *
     * @return \Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function admin()
    {
        return view('cms::admin');
    }


    /**
     * Show the page for a given URL.
     *
     * @param string $path Page URL segment
     * @param string $domain Requested domain
     * @return View|Response|RedirectResponse|string Response of the controller action
     */
    public function index( Request $request, string $path, $domain = '' )
    {
        $path = trim( $path, '/' ) ?: '/';

        if( Permission::can( 'page:view', $request->user() ) )
        {
            $version = Version::where( 'versionable_type', Page::class )
                ->where( 'data->domain', $domain )
                ->where( 'data->path', $path )
                ->orderByDesc( 'id' )
                ->take( 1 )
                ->first();

            $page = $version
                ? Page::where( 'id', $version->versionable_id )->firstOrFail()
                : Page::where( 'domain', $domain )->where( 'path', $path )->firstOrFail();

            $page->cache = 0; // don't cache sub-parts in preview requests

            if( $to = $version?->data?->to ?? $page->to ) {
                return str_starts_with( $to, 'http' ) ? redirect()->away( $to ) : redirect( $to );
            }

            $content = collect( $version->aux?->content ?? $page->content ?? [] )->groupBy( 'group' );

            $views = [( cms( $page, 'theme' ) ?: 'cms' ) . '::' . ( cms( $page, 'type' ) ?: 'page' ), 'cms::page'];
            return view()->first( $views, ['page' => $page, 'content' => $content] );
        }

        $cache = Cache::store( config( 'cms.cache', 'file' ) );
        $key = Page::key( $path, $domain );

        if( $html = $cache->get( $key ) ) {
            return $html;
        }

        $page = Page::where( 'path', $path )
            ->where( 'domain', $domain )
            ->where( 'status', '>', 0 )
            ->firstOrFail();

        if( $to = $page->to ) {
            return str_starts_with( $to, 'http' ) ? redirect()->away( $to ) : redirect( $to );
        }

        $content = collect( $page->content ?? [] )->groupBy( 'group' );

        $views = [( cms( $page, 'theme' ) ?: 'cms' ) . '::' . ( cms( $page, 'type' ) ?: 'page' ), 'cms::page'];
        $html = view()->first( $views, ['page' => $page, 'content' => $content] )->render();

        if( $page->cache ) {
            $cache->put( $key, $html, now()->addMinutes( (int) $page->cache ) );
        }

        return $html;
    }
}
