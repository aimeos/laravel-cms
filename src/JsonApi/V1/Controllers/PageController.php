<?php

namespace Aimeos\Cms\JsonApi\V1\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;
use Aimeos\Cms\JsonApi\V1\Pages\PageCollectionQuery;
use Aimeos\Cms\JsonApi\V1\Pages\PageQuery;


class PageController extends JsonApiController
{
    public function read( ?Post $post, PostCollectionQuery $query )
    {
        $data = array_merge( $data, ['meta' => ['baseurl' => Storage::url( '' )]] );
        return DataResponse::make( $data )->withQueryParameters( $query );
    }


    public function searched( $data, PostCollectionQuery $query )
    {
        $data = array_merge( $data, ['meta' => ['baseurl' => Storage::url( '' )]] );
        return DataResponse::make( $data )->withQueryParameters( $query );
    }
}