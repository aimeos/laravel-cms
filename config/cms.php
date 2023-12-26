<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database connection
    |--------------------------------------------------------------------------
    |
    | Use the database connection defined in ./config/database.php to manage
    | page, content and file records.
    |
    */
    'db' => env( 'DB_CONNECTION', 'mysql' ),

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
    | Number of days after deleted pages, contents and files will be finally
    | removed. Disable pruning with FALSE as value.
    |
    */
    'prune' => 30,

    /*
    |--------------------------------------------------------------------------
    | Page template
    |--------------------------------------------------------------------------
    |
    | The configured Blade template will be used for rendering all CMS pages.
    | It must extend from a page Blade template which contains the HTML
    | head/body sections as well as the navigation.
    |
    */
    'view' => 'cms::content',
];