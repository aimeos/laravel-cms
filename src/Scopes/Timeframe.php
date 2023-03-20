<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;


/**
 * Time frame scope for limiting query results.
 */
class Timeframe implements Scope
{
    /**
     * Applys additional restrictions to the query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder Query builder
     * @param \Illuminate\Database\Eloquent\Model $model Eloquent model
     */
    public function apply( Builder $builder, Model $model )
    {
        if( empty( Auth::user()->cmseditor ) )
        {
            $builder
                ->where( function( Builder $query ) use ( $model ) {
                    return $query->where( $model->qualifyColumn( 'start' ), null )
                        ->orWhere( $model->qualifyColumn( 'start' ), '>=', date( 'Y-m-d H:i:00' ) );
                } )
                ->where( function( Builder $query ) use ( $model ) {
                    return $query->where( $model->qualifyColumn( 'end' ), null )
                        ->orWhere( $model->qualifyColumn( 'end' ), '<=', date( 'Y-m-d H:i:00' ) );
                } );
        }
    }


    /**
     * Adds additional macros to the query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder Query builder
     */
    public function extend( Builder $builder )
    {
        $builder->macro( 'withoutTimeframe', function( Builder $builder ) {
            return $builder->withoutGlobalScope( $this );
        });
    }
}