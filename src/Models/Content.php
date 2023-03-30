<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Models;

use Aimeos\Cms\Concerns\Tenancy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * Content model
 */
class Content extends Model
{
    use HasUuids;
    use SoftDeletes;
    use MassPrunable;
    use Tenancy;


    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'tenant_id' => '',
        'lang' => '',
        'data' => '{}',
        'editor' => '',
    ];

    /**
     * The automatic casts for the attributes.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'object',
        'lang' => 'string',
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
        'lang',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_contents';


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
     * Get the page's latest head/meta data.
     */
    public function latest() : HasOne
    {
        return $this->hasOne( Version::class, 'versionable_id' )
            ->where( 'versionable_type', Content::class )
            ->orderBy( 'id', 'desc' )
            ->take( 1 );
    }


    /**
     * Generate a new UUID for the model.
     */
    public function newUniqueId() : string
    {
        return (string) new \Symfony\Component\Uid\UuidV7();
    }


    /**
     * Get the pages the content is referenced by.
     */
    public function pages() : BelongsToMany
    {
        return $this->belongsToMany( Page::class, 'cms_page_content' )->as( 'ref' )
            ->withPivot( 'id', 'position', 'status', 'editor', 'created_at', 'updated_at' )
            ->wherePivot( 'tenant_id', \Aimeos\Cms\Tenancy::value() )
            ->withTimestamps();
    }


    /**
     * Get the content's published version.
     */
    public function published() : HasOne
    {
        return $this->hasOne( Version::class, 'versionable_id' )
            ->where( 'versionable_type', Content::class )
            ->where( 'published', true )
            ->orderBy( 'id', 'desc' )
            ->take( 1 );
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
        return static::withoutTenancy()->where( 'id', '' );
    }


    /**
     * Get all of the content's versions.
     */
    public function versions() : MorphMany
    {
        return $this->morphMany( Version::class, 'versionable' );
    }
}
