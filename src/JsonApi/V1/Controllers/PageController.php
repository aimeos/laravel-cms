<?php

namespace Aimeos\Cms\JsonApi\V1\Controllers;

use Illuminate\Support\Facades\Storage;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\RelatedResponse;
use LaravelJsonApi\Eloquent\Fields\Relations\Relation;
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use Aimeos\Cms\JsonApi\V1\Pages\PageCollectionQuery;
use Aimeos\Cms\JsonApi\V1\Pages\PageQuery;
use Aimeos\Cms\Models\Page;


/**
 * Custom controller to add meta data to responses.
 */
class PageController extends JsonApiController
{
    /**
     * Adds global meta data to single resource response.
     *
     * @param Page|null $page Page model
     * @param PageQuery $query Page query
     * @return DataResponse
     */
    public function read( ?Page $page, PageQuery $query ) : DataResponse
    {
        return DataResponse::make( $page )
            ->withMeta( ['baseurl' => Storage::disk( config( 'cms.disk', 'public' ) )->url( '' )] )
            ->withQueryParameters( $query );
    }


    /**
     * Adds global meta data to related resource response.
     *
     * @param Page|null $page Page model
     * @param mixed $data Fetched data
     * @param ResourceQuery $request Query object
     * @param Relation Relation type object
     * @return RelatedResponse
     */
    public function readRelatedContent( ?Page $page, $data, ResourceQuery $request ) : RelatedResponse
    {
        return RelatedResponse::make( $page, 'contents', $data )
            ->withMeta( ['baseurl' => Storage::disk( config( 'cms.disk', 'public' ) )->url( '' )] )
            ->withQueryParameters( $request );
    }


    /**
     * Adds global meta data to collection resource response.
     *
     * @param mixed $data Fetched data
     * @param PageCollectionQuery $query Page collection query
     * @return DataResponse
     */
    public function searched( $data, PageCollectionQuery $query ) : DataResponse
    {
        return DataResponse::make( $data )
            ->withMeta( ['baseurl' => Storage::disk( config( 'cms.disk', 'public' ) )->url( '' )] )
            ->withQueryParameters( $query );
    }
}