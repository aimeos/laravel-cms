<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Ref;


final class PubRef
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Ref
    {
        $ref = Ref::findOrFail( $args['id'] );
        $ref->editor = Auth::user()?->name ?? request()->ip();
        $ref->published = true;

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $ref ) {

            Ref::where( 'page_id', $ref->page_id )
                ->where( 'content_id', $ref->content_id )
                ->where( 'published', true )
                ->delete();

            $ref->save();
        }, 3 );

        return $ref;
    }
}
