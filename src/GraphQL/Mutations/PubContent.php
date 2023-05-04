<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Content;


final class PubContent
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Content
    {
        $content = Content::findOrFail( $args['id'] );

        if( $content->latest )
        {
            DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $content ) {

                $latest = $content->latest;
                $latest->published = true;
                $latest->save();

                $content->data = $latest->data;
                $content->editor = Auth::user()?->name ?? request()->ip();
                $content->save();

            }, 3 );
        }

        return $content;
    }
}
