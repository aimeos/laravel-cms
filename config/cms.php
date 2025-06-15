<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache store
    |--------------------------------------------------------------------------
    |
    | Use the cache store defined in ./config/cache.php to store rendered pages
    | for fast response times.
    |
    */
    'cache' => env( 'APP_DEBUG' ) ? 'array' : 'file',

    /*
    |--------------------------------------------------------------------------
    | Database connection
    |--------------------------------------------------------------------------
    |
    | Use the database connection defined in ./config/database.php to manage
    | page, element and file records.
    |
    */
    'db' => env( 'DB_CONNECTION', 'mysql' ),

    /*
    |--------------------------------------------------------------------------
    | Filesystem disk
    |--------------------------------------------------------------------------
    |
    | Use the filesystem disk defined in ./config/filesystems.php to store the
    | uploaded files. By default, they are stored in the ./public/storage/cms/
    | folder but this can be any supported cloud storage too.
    |
    */
    'disk' => env( 'CMS_DISK', 'public' ),

    /*
    |--------------------------------------------------------------------------
    | Prune deleted records
    |--------------------------------------------------------------------------
    |
    | Number of days after deleted pages, elements and files will be finally
    | removed. Disable pruning with FALSE as value.
    |
    */
    'prune' => 30,

    /*
    |--------------------------------------------------------------------------
    | Number of stored versions
    |--------------------------------------------------------------------------
    |
    | Number of versions to keep for each page, element and file. If the
    | number of versions exceeds this value, the oldest versions will be
    | deleted.
    |
    */
    'versions' => 10,
];