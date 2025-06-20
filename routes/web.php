<?php

use Aimeos\Cms\Http\Controllers;
use Illuminate\Support\Facades\Route;


\LaravelJsonApi\Laravel\Facades\JsonApiRoute::server( "cms" )->prefix( "cms" )->resources( function( $server ) {
    $server->resource( "pages", \Aimeos\Cms\JsonApi\V1\Controllers\JsonapiController::class )->readOnly();
});

Route::get('cmsadmin/{path?}', [Controllers\PageController::class, 'admin'])
    ->where(['path' => '.*'])
    ->name('cms.admin');

Route::group(config('cms.multidomain') ? ['domain' => '{domain}'] : [], function() {
    Route::get('{path?}', [Controllers\PageController::class, 'index'])
        ->name('cms.page');
});
