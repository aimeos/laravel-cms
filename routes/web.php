<?php

use Aimeos\Cms\Http\Controllers;
use Illuminate\Support\Facades\Route;


\LaravelJsonApi\Laravel\Facades\JsonApiRoute::server( "cms" )->prefix( "cms" )->resources( function( $server ) {
    $server->resource( "pages", \Aimeos\Cms\JsonApi\V1\Controllers\JsonapiController::class )->readOnly();
});

Route::get('cmsadmin/{path?}', [Controllers\AdminController::class, 'index'])
    ->where(['path' => '.*'])
    ->name('cms.admin');

Route::match(['GET', 'HEAD', 'OPTIONS'], 'cmsproxy', [Controllers\AdminController::class, 'proxy'])
    ->middleware(config('cms.proxy.middleware', ['web', 'auth', 'throttle:20,1']))
    ->name('cms.proxy');

Route::post('cmsapi/contact', [Controllers\ApiController::class, 'contact'])
    ->middleware('throttle:2,1')
    ->name('cms.api.contact');

Route::group(config('cms.multidomain') ? ['domain' => '{domain}'] : [], function() {
    Route::get('{path?}', [Controllers\PageController::class, 'index'])
        ->middleware(['web'])
        ->name('cms.page');
});
