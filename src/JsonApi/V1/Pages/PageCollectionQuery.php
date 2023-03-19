<?php

namespace Aimeos\Cms\JsonApi\V1\Pages;

use LaravelJsonApi\Validation\Rule as JsonApiRule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;


class PageCollectionQuery extends ResourceQuery
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'fields' => [
                'nullable',
                'array',
                JsonApiRule::fieldSets(),
            ],
            'filter' => [
                'nullable',
                'array',
                JsonApiRule::filter(),
            ],
            'include' => [
                'nullable',
                'string',
                JsonApiRule::includePaths()->forget(
                    'anchestors.content',
                    'children.children',
                    'subtree.content',
                ),
            ],
            'page' => [
                'nullable',
                'array',
                JsonApiRule::page(),
            ],
            'page.number' => [
                'integer',
                'between:1,100',
            ],
            'page.size' => [
                'integer',
                'between:1,25',
            ],
            'sort' => JsonApiRule::notSupported(),
        ];
    }
}
