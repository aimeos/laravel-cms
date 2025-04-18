<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Models;

use Aimeos\Cms\Concerns\Tenancy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * Version model
 */
class Version extends Model
{
    use MassPrunable;
    use HasUuids;
    use Tenancy;


    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'tenant_id' => '',
        'data' => '{}',
        'refs' => '[]',
        'published' => false,
        'editor' => '',
    ];

    /**
     * The automatic casts for the attributes.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'object',
        'refs' => 'array',
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
        'editor',
        'data',
        'refs',
    ];

    /**
     * Indicates if the model's ID isn't auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_versions';


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
     * Get all files referenced by the versioned data.
     */
    public function files() : BelongsToMany
    {
        return $this->belongsToMany( File::class, 'cms_version_file' );
    }


    /**
     * Generate a new UUID for the model.
     */
    public function newUniqueId() : string
    {
        return (string) new \Symfony\Component\Uid\UuidV7();
    }


    /**
     * Get the prunable model query.
     */
    public function prunable() : Builder
    {
        if( is_int( $days = config( 'cms.prune' ) ) )
        {
            return static::withoutTenancy()
                ->where( 'updated_at', '<=', now()->subDays( $days ) )
                ->where( 'published', false );
        }

        // pruning is disabled
        return static::withoutTenancy()->where( 'id', '' );
    }


    /**
     * Get the parent versionable model (page or content).
     */
    public function versionable() : MorphTo
    {
        return $this->morphTo();
    }
}
