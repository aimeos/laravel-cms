<?php

namespace Aimeos\Cms\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
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
     */
    public function index( string $slug, string $lang = '' ): View
    {
        return view( 'cms::page', [
            'page' => Page::where( 'slug', $slug )->where( 'lang', $lang )->firstOrFail()
        ] );
    }
}
