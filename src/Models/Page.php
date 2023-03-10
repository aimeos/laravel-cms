<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Models;

use Aimeos\Cms\Concerns\Tenancy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Kalnoy\Nestedset\NodeTrait;
use Kalnoy\Nestedset\AncestorsRelation;
use Kalnoy\Nestedset\DescendantsRelation;


/**
 * Page model
 */
class Page extends Model
{
    use SoftDeletes;
    use Prunable;
    use NodeTrait;
    use Tenancy;


    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'tenant_id' => '',
        'tag' => '',
        'lang' => '',
        'slug' => '',
        'domain' => '',
        'to' => '',
        'name' => '',
        'title' => '',
        'data' => '{}',
        'config' => '{}',
        'status' => 0,
        'cache' => 5,
        'editor' => '',
    ];

    /**
     * The automatic casts for the attributes.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'object',
        'config' => 'object',
    ];

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'sqlite';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tag',
        'lang',
        'slug',
        'domain',
        'to',
        'name',
        'title',
        'config',
        'status',
        'cache',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_pages';


    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct( array $attributes = [] )
    {
        $this->connection = config( 'cms.db', 'sqlite' );

        parent::__construct( $attributes );
    }


    /**
     * Get query for ancestors of the node.
     *
     * @return AncestorsRelation
     */
    public function ancestors() : AncestorsRelation
    {
        return (new AncestorsRelation( $this->newQuery(), $this ))->defaultOrder();
    }


    /**
     * Get the active content for the page.
     */
    public function content(): BelongsToMany
    {
        return $this->belongsToMany( Content::class, 'cms_page_content' )
            ->wherePivot( 'tenant_id', \Aimeos\Cms\Tenancy::value() )
            ->wherePivot( 'status', 1 )
            ->withPivot( 'position' )
            ->orderByPivot( 'position' );
    }


    /**
     * Get all content for the page.
     */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany( Content::class, 'cms_page_content' )->as( 'ref' )
            ->withPivot( 'id', 'position', 'status', 'editor', 'created_at', 'updated_at' )
            ->wherePivot( 'tenant_id', \Aimeos\Cms\Tenancy::value() )
            ->withTimestamps()
            ->orderByPivot( 'position' );
    }


    /**
     * Returns the cache key for the page.
     *
     * @param Page|string $page Page object or URL slug
     * @param string $lang ISO language code
     * @param string $domain Domain name
     * @return string Cache key
     */
    public static function key( $page, string $lang = '', string $domain = '' ) : string
    {
        if( $page instanceof Page ) {
            return md5( \Aimeos\Cms\Tenancy::value() . '/' . $page->domain . '/' . $page->slug . '/' . $page->lang );
        }

        return md5( \Aimeos\Cms\Tenancy::value() . '/' . $domain . '/' . $page . '/' . $lang );
    }


    /**
     * Get the page's latest head/meta data.
     */
    public function latest() : HasOne
    {
        return $this->hasOne( Version::class, 'versionable_id' )
            ->where( 'versionable_type', Page::class )
            ->orderBy( 'id', 'desc' )
            ->take( 1 );
    }


    /**
     * Get the navigation for the page.
     */
    public function nav() : \Kalnoy\Nestedset\Collection
    {
        return $this->ancestors->first()?->descendants->toTree() ?: new \Kalnoy\Nestedset\Collection();
    }


    /**
     * Get the prunable model query.
     */
    public function prunable() : Builder
    {
        if( is_int( $days = config( 'cms.prune' ) ) ) {
            return static::withoutTenancy()->where( 'deleted_at', '<=', now()->subDays( $days ) );
        }

        // pruning is disabled
        return static::withoutTenancy()->where( 'id', -1 );
    }


    /**
     * Get the page's published head/meta data.
     */
    public function published() : HasOne
    {
        return $this->hasOne( Version::class, 'versionable_id' )
            ->where( 'versionable_type', Page::class )
            ->where( 'published', true )
            ->orderBy( 'id', 'desc' )
            ->take( 1 );
    }


    /**
     * Get query for the complete sub-tree up to three levels.
     *
     * @return DescendantsRelation
     */
    public function subtree() : DescendantsRelation
    {
        // restrict max. depth to three levels for performance reasons
        $builder = $this->newScopedQuery()
            ->withDepth()
            ->whereNotExists( function( \Illuminate\Database\Query\Builder $query ) {
                $query->select( DB::raw( 1 ) )
                    ->from( $this->getTable() . ' AS parent' )
                    ->whereColumn( $this->qualifyColumn( '_lft' ), '>=', 'parent._lft' )
                    ->whereColumn( $this->qualifyColumn( '_rgt' ), '<=', 'parent._rgt' )
                    ->where( 'parent.tenant_id', '=', \Aimeos\Cms\Tenancy::value() )
                    ->where( 'parent.status', '<=', 0 );
            } )
            ->groupBy(
                'id', 'tenant_id', 'lang', 'name', 'title',
                'slug', 'to', 'tag', 'data', 'config', 'status',
                'cache', '_lft', '_rgt', 'parent_id', 'editor',
                'created_at', 'updated_at', 'deleted_at'
            )
            ->having( 'depth', '<=', ( $this->depth ?? 0 ) + 3 );

        return (new DescendantsRelation( $builder, $this ))->defaultOrder();
    }


    /**
     * Get all of the page's versions.
     */
    public function versions() : MorphMany
    {
        return $this->morphMany( Version::class, 'versionable' );
    }
}
