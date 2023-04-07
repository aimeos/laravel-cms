<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Ref;


final class MoveRef
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Ref
    {
        $pos = $args['pos'] ?? 0;
        $ref = Ref::findOrFail( $args['id'] );
        $ref->editor = Auth::user()?->name ?? request()->ip();

        $db = DB::connection( config( 'cms.db', 'sqlite' ) );

        $db->transaction( function() use ( $db, $ref, $pos ) {
            $db->table( 'cms_page_content' )
                ->where( 'position', '>=', $ref->position )
                ->update( ['position' => DB::raw( 'position - 1')] );

            $db->table( 'cms_page_content' )
                ->where( 'position', '>=', $pos )
                ->update( ['position' => DB::raw( 'position + 1')] );

            $ref->position = $pos;
            $ref->save();
        }, 3 );

        return $ref;
    }
}
