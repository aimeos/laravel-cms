<?php

namespace Aimeos\Cms\Tools;

use Prism\Prism\Tool;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Page;


class AddPage extends Tool
{
    private int $numcalls = 0;


    public function __construct()
    {
        $this->as( 'create-page' )
            ->for( 'Creates a new page and adds it to the page tree. Returns the added page and its content as JSON object.' )
            ->withStringParameter( 'lang', 'ISO language code from get-locales tool call, e.g "en" or "en-GB".' )
            ->withStringParameter( 'title', 'SEO optimized page title in the language of the page. Must be unique for each page' )
            ->withStringParameter( 'name', 'Short name of the page for menus in the language of the page. Should not be longer than 30 characters.' )
            ->withStringParameter( 'content', 'Page content in the language of the page and in markdown format.' )
            ->withNumberParameter( 'parent_id', 'ID of the parent page from the pages tool call the new page will be added below.', false )
            ->using( $this );
    }


    public function __invoke( string $lang, string $title, string $name, string $content, ?int $parent_id = null ): string
    {
        if( $this->numcalls > 0 ) {
            return response()->json( ['error' => 'Only one page can be created at a time.'] );
        }

        $page = new Page();
        $path = trim( Str::slug( $title, '-' ), '-' );
        $editor = Auth::user()?->name ?? request()->ip();
        $elements = [[
            'id' => $this->uid(),
            'type' => 'text',
            'group' => 'main',
            'data' => [
                'text' => $content,
            ]
        ]];

        $page->tenant_id = \Aimeos\Cms\Tenancy::value();
        $page->editor = $editor;
        $page->fill( [
            'lang' => $lang,
            'name' => $name,
            'title' => $title,
            'path' => $path,
            'content' => $elements,
        ] );

        $version = [
            'lang' => $lang,
            'editor' => $editor,
            'data' => [
                'lang' => $lang,
                'name' => $name,
                'title' => $title,
                'path' => $path,
            ],
            'aux' => [
                'content' => $elements,
            ]
        ];

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $parent_id, $page, $version ) {

            if( $parent_id !== null ) {
                $page->beforeNode( Page::where( 'parent_id', $parent_id )->orderBy( '_lft', 'asc' )->firstOrFail() );
            }

            $page->save();
            $page->versions()->create( $version );

        }, 3 );

        $this->numcalls++;
        return response()->json( $page );
    }


    public function uid(): string
    {
        $base64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
        $epoch = strtotime( '2025-01-01T00:00:00Z' ) * 1000;

        // IDs will repeat after ~70 years
        $value = ( ( (int) ( ( microtime( true ) * 1000 - $epoch ) / 256 ) ) << 3 );

        $id = '';
        for( $i = 0; $i < 6; $i++ )
        {
            // First character: only A-Z/a-z (index % 52), others: full 64-character set
            $index = ($value >> 6 * (5 - $i)) & 63;
            $id .= $base64[$i === 0 ? $index % 52 : $index];
        }

        return $id;
    }
}
