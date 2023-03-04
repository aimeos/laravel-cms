<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Models;

use Aimeos\Cms\Concerns\Tenancy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    use HasFactory;
    use SoftDeletes;
    use Prunable;
    use NodeTrait;
    use Tenancy;


    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'sqlite';

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
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'tenant_id' => '',
        'lang' => '',
        'name' => '',
        'title' => '',
        'slug' => '',
        'to' => '',
        'tag' => '',
        'data' => '{}',
        'config' => '{}',
        'status' => 0,
        'cache' => null,
        'editor' => '',
    ];

    /**
     * The automatic casts for the attributes.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lang',
        'name',
        'title',
        'slug',
        'to',
        'tag',
        'status',
        'cache',
    ];


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
            ->withPivot( 'tenant_id', 'position', 'status' )
            ->wherePivot( 'status', 1 )
            ->orderByPivot( 'position' );
    }


    /**
     * Get all content for the page.
     */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany( Content::class, 'cms_page_content' )
            ->withPivot( 'tenant_pid', 'position', 'status', 'editor', 'created_at', 'updated_at' )
            ->withTimestamps()
            ->orderByPivot( 'position' );
    }


    /**
     * Get query for descendants of the node.
     *
     * @return DescendantsRelation
     */
    public function descendants() : DescendantsRelation
    {
        // restrict max. depth to three levels for performance reasons
        $builder = $this->newScopedQuery()
            ->withDepth()
            ->where( 'status', 1 )
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
     * Returns the cache key for the page.
     *
     * @param string $slug Unique tag to retrieve page tree
     * @param string $lang ISO language code
     * @return string Cache key
     */
    public static function key( string $slug, string $lang ): string
    {
        return md5( \Aimeos\Cms\Tenancy::value() . '/' . $slug . '/' . $lang );
    }


    /**
     * Get the navigation for the page.
     */
    public function nav(): \Kalnoy\Nestedset\Collection
    {
        return $this->ancestors->first()?->descendants->toTree() ?: new \Kalnoy\Nestedset\Collection();
    }


    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        if( is_int( $days = config( 'cms.prune' ) ) ) {
            return static::withoutTenancy()->where( 'deleted_at', '<=', now()->subDays( $days ) );
        }

        // pruning is disabled
        return static::withoutTenancy()->where( 'id', -1 );
    }
}
