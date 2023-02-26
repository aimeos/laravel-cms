<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Kalnoy\Nestedset\NodeTrait;
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
        'lang' => '',
        'name' => '',
        'title' => '',
        'slug' => '',
        'to' => null,
        'tag' => null,
        'data' => '[]',
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
        'data',
        'status',
        'cache',
    ];


    /**
     * Get the content for the page.
     */
    public function content(): HasOne
    {
        $rel = $this->hasOne( Content::class );

        return ( $cid = request()->input( 'cid' ) )
            ? $rel->where( 'id', $cid )
            : $rel->where( 'status', '>', 0 )->orderBy( 'id', 'desc' );
    }


    /**
     * Get the revisions for the page.
     */
    public function contents(): HasMany
    {
        return $this->hasMany( Content::class )
            ->orderBy( 'id', 'desc' );
    }


    /**
     * Get query for descendants of the node.
     *
     * @return DescendantsRelation
     */
    public function descendants()
    {
        // restrict max. depth to three levels for performance reasons
        $builder = $this->newQuery()->withDepth()->having( 'depth', '<=', ( $this->depth ?? 0 ) + 3 );

        return new DescendantsRelation( $builder, $this );
    }


    /**
     * Get the latest revision for the page.
     */
    public function latest(): HasOne
    {
        return $this->hasOne( Content::class )
            ->orderBy( 'id', 'desc' );
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
        return md5( $slug . '/' . $lang );
    }


    /**
     * Get the latest revision for the page.
     *
     * @param string $tag Unique tag to retrieve page tree
     * @param string $lang ISO language code
     */
    public static function nav( string $tag, string $lang = '' ): ?Page
    {
        $node = Page::withDepth()
            ->where( 'tag', $tag )
            ->where( 'lang', $lang )
            ->where( 'status', 1 )
            ->first();

        if( !$node ) {
            return null;
        }

        $descendants = $node->descendants()
            ->withDepth()
            ->where( 'status', 1 )
            ->having( 'depth', '<=', $node->depth + 3 )
            ->get()
            ->toTree();

        return $node->setRelation( 'children', $descendants );
    }


    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        if( is_int( $days = config( 'cms.prune' ) ) ) {
            return static::where( 'deleted_at', '<=', now()->subDays( $days ) );
        }

        // pruning is disabled
        return static::where( 'id', -1 );
    }
}
