<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Cache;
use Aimeos\Cms\Models\Page;


final class PubPage
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Page
    {
        $page = Page::findOrFail( $args['id'] );

        if( $latest = $page->latest )
        {
            if( $args['at'] ?? null )
            {
                $latest->publish_at = $args['at'];
                $latest->save();
                return $page;
            }

            $page->publish( $latest );
            Cache::forget( Page::key( $page ) );
        }

        return $page;
    }
}
