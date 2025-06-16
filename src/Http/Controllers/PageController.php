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
     * Return the admin page view.
     *
     * @return \Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function admin(): View
    {
        return view('cms::admin');
    }


    /**
     * Show the page for a given URL.
     *
     * @param string $slug Page URL segment
     * @param string $domain Requested domain
     * @return string HTML code
     */
    public function index( Request $request, string $slug, $domain = '' ): string
    {
        if( empty( $slug ) ) {
            $slug = '/';
        }

        $cache = Cache::store( config( 'cms.cache', 'file' ) );
        $key = Page::key( $slug, $domain );

        if( $html = $cache->get( $key ) ) {
            return $html;
        }

        $page = Page::where( 'path', $path )
            ->where( 'domain', $domain )
            ->where( 'status', '>', 0 )
            ->firstOrFail();

        if( $page->to ) {
            return !str_starts_with( $page->to, 'http' ) ? redirect( $page->to ) : redirect()->away( $page->to );
        }

        $data = $page->toArray();
        $data['files'] = ( $page->latest?->files ?? $page->files )->pluck( null, 'id');
        $data['elements'] = ( $page->latest?->elements ?? $page->elements )->pluck( null, 'id');
        $data['page'] = $page;

        $views = [
            $data['theme'] && $data['type'] ? $data['theme'] . '::' . $data['type'] : null,
            $data['theme'] ? $data['theme'] . '::page' : null,
            'cms::page'
        ];

        $html = view()->first( array_filter( $views ), $data )->render();

        if( $page->cache ) {
            $cache->put( $key, $html, now()->addMinutes( (int) $page->cache ) );
        }

        return $html;
    }


    /**
     * Preview the page for a given page ID.
     *
     * @param string $id Page ID
     * @return string HTML code
     */
    public function preview( Request $request, string $id ): string
    {
        if( !Gate::allowIf( fn( $user ) => $user->cmseditor > 0 ) ) {
            abort( 401 );
        }

        $page = Page::findOrFail( $id );

        if( $page->to ) {
            return !str_starts_with( $page->to, 'http' ) ? redirect( $page->to ) : redirect()->away( $page->to );
        }

        $data = (array) $page->latest?->data ?? $page->toArray();
        $data['contents'] = $page->latest?->contents ?? $page->contents;
        $data['files'] = ( $page->latest?->files ?? $page->files )->pluck( null, 'id');
        $data['elements'] = ( $page->latest?->elements ?? $page->elements )->pluck( null, 'id');
        $data['cache'] = 0; // don't cache sub-parts in preview requests
        $data['page'] = $page;

        $views = [
            $data['theme'] && $data['type'] ? $data['theme'] . '::' . $data['type'] : null,
            $data['theme'] ? $data['theme'] . '::page' : null,
            'cms::page'
        ];

        return view()->first( array_filter( $views ), $data )->render();
    }
}
