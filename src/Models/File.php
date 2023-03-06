<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Models;

use Aimeos\Cms\Concerns\Tenancy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'previews' => 'object',
    ];

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
     * Generate a new UUID for the model.
     */
    public function newUniqueId(): string
    {
        return (string) new \Symfony\Component\Uid\UuidV7();
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
        return static::withoutTenancy()->where( 'id', '' );
    }


    /**
     * Prepare the model for pruning.
     */
    protected function pruning(): void
    {
        $store = Storage::disk( config( 'cms.disk', 'public' ) );

        foreach( $this->previews as $path ) {
            $store->delete( $path );
        }

        $store->delete( $this->path );
    }
}
