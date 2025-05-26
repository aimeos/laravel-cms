<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Aimeos\Cms\Models\File;


final class PubFile
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : File
    {
        $file = File::findOrFail( $args['id'] );

        if( $latest = $file->latest )
        {
            if( $args['at'] ?? null )
            {
                $latest->publish_at = $args['at'];
                $latest->save();
                return $file;
            }

            $file->publish( $latest );
        }

        return $file;
    }
}
