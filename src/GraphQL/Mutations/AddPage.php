<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Page;


final class AddPage
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Page
    {
        $page = new Page();

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $page, $args ) {

            $editor = Auth::user()?->name ?? request()->ip();

            $page->fill( $args['input'] ?? [] );
            $page->tenant_id = \Aimeos\Cms\Tenancy::value();
            $page->editor = $editor;

            if( $page->status > 0 && isset( $args['input']['data'] ) ) {
                $page->data = $args['input']['data'];
            }

            if( isset( $args['ref'] ) ) {
                $page->beforeNode( Page::findOrFail( $args['ref'] ) );
            }
            elseif( isset( $args['parent'] ) ) {
                $page->appendToNode( Page::findOrFail( $args['parent'] ) );
            }

            $page->save();

            if( isset( $args['input']['data'] ) )
            {
                $version = $page->versions()->create( [
                    'data' => $args['input']['data'],
                    'published' => $page->status > 0 ? true : false,
                    'editor' => $editor
                ] );

                $version->files()->attach( $args['files'] ?? [] );
            }
        }, 3 );

        return $page;
    }
}
