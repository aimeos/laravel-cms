<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Models;

use Aimeos\Cms\Concerns\Tenancy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


/**
 * File model
 */
class File extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Prunable;
    use Tenancy;


    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'tenant_id' => '',
        'mime' => '',
        'tag' => '',
        'name' => '',
        'path' => '',
        'previews' => '{}',
        'editor' => '',
    ];

    /**
     * The automatic casts for the attributes.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'previews' => 'object',
        'tag' => 'string',
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
        'name',
        'tag',
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
    protected $table = 'cms_files';


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
     * Interact with the name property.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn($value) => (string) $value,
        );
    }


    /**
     * Generate a new UUID for the model.
     */
    public function newUniqueId() : string
    {
        return (string) new \Symfony\Component\Uid\UuidV7();
    }


    /**
     * Get all content referencing the file.
     */
    public function versions() : BelongsToMany
    {
        return $this->belongsToMany( Version::class, 'cms_version_file' );
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
     * Prepare the model for pruning.
     */
    protected function pruning() : void
    {
        $store = Storage::disk( config( 'cms.disk', 'public' ) );

        foreach( $this->previews as $path ) {
            $store->delete( $path );
        }

        $store->delete( $this->path );
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
}
