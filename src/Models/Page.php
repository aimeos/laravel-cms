<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Models;

use Aimeos\Cms\Concerns\Tenancy;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'start' => null,
        'end' => null,
        'editor' => '',
    ];

    /**
     * The automatic casts for the attributes.
     *
     * @var array
     */
    protected $casts = [
        'tag' => 'string',
        'lang' => 'string',
        'slug' => 'string',
        'domain' => 'string',
        'to' => 'string',
        'name' => 'string',
        'title' => 'string',
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
        'start',
        'end',
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
     * Apply default ordering to all queries
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('treeorder', function( Builder $builder ) {
            $builder->defaultOrder();
        });
    }


    /**
     * Interact with the cache property.
     */
    protected function cache(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value === null ? 5 : (int) $value,
        );
    }


    /**
     * Get the active content for the page.
     */
    public function content() : BelongsToMany
    {
        $ref = new Ref;

        return $this->belongsToMany( Content::class, 'cms_page_content' )
            ->where( function( Builder $query ) use ( $ref ) {
                return $query
                    ->where( function( Builder $query ) use ( $ref ) {
                        return $query->where( $ref->qualifyColumn( 'start' ), '>=', date( 'Y-m-d H:i:00' ) )
                            ->orWhere( $ref->qualifyColumn( 'start' ), null );
                    } )
                    ->where( function( Builder $query ) use ( $ref ) {
                        return $query->where( $ref->qualifyColumn( 'end' ), '<=', date( 'Y-m-d H:i:00' ) )
                            ->orWhere( $ref->qualifyColumn( 'end' ), null );
                    } )
                    ->where( $ref->qualifyColumn( 'tenant_id' ), \Aimeos\Cms\Tenancy::value() )
                    ->where( $ref->qualifyColumn( 'published' ), true )
                    ->where( $ref->qualifyColumn( 'status' ), 1 );
            } )
            ->withPivot( 'position' )
            ->orderByPivot( 'position' );
    }


    /**
     * Get all content for the page.
     */
    public function contents() : BelongsToMany
    {
        return $this->belongsToMany( Content::class, 'cms_page_content' )->as( 'ref' )
            ->withPivot( 'id', 'published', 'position', 'status', 'editor', 'created_at', 'updated_at' )
            ->wherePivot( 'tenant_id', \Aimeos\Cms\Tenancy::value() )
            ->withTimestamps()
            ->orderByPivot( 'position' );
    }


    /**
     * Interact with the domain property.
     */
    protected function domain(): Attribute
    {
        return Attribute::make(
            set: fn($value) => (string) $value,
        );
    }


    /**
     * Tests if node has children.
     *
     * @return bool TRUE if node has children, FALSE if not
     */
    public function getHasAttribute()
    {
        return $this->_rgt > $this->_lft + 1;
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
     * Interact with the lang property.
     */
    protected function lang(): Attribute
    {
        return Attribute::make(
            set: fn($value) => (string) $value,
        );
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
     * Interact with the name property.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn($value) => (string) $value,
        );
    }


    /**
     * Get the navigation for the page.
     */
    public function nav() : \Kalnoy\Nestedset\Collection
    {
        return $this->ancestors->first()?->subtree->toTree() ?: new \Kalnoy\Nestedset\Collection();
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
     * Interact with the slug property.
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn($value) => (string) $value,
        );
    }


    /**
     * Interact with the status property.
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            set: fn($value) => (int) $value,
        );
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

        return (new DescendantsRelation( $builder, $this ));
    }


    /**
     * Interact with the tag property.
     */
    protected function tag(): Attribute
    {
        return Attribute::make(
            set: fn($value) => (string) $value,
        );
    }


    /**
     * Interact with the title property.
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            set: fn($value) => (string) $value,
        );
    }


    /**
     * Interact with the to property.
     */
    protected function to(): Attribute
    {
        return Attribute::make(
            set: fn($value) => (string) $value,
        );
    }


    /**
     * Get all of the page's versions.
     */
    public function versions() : MorphMany
    {
        return $this->morphMany( Version::class, 'versionable' );
    }
}
