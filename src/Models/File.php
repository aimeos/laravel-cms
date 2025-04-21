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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'tag',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_files';


    /**
     * Get the connection name for the model.
     */
    public function getConnectionName()
    {
        return config( 'cms.db', 'sqlite' );
    }


    /**
     * Get all versions referencing the file.
     */
    public function versions() : BelongsToMany
    {
        return $this->belongsToMany( Version::class, 'cms_file_version' );
    }


    /**
     * Get the prunable model query.
     */
    public function prunable() : Builder
    {
        if( is_int( $days = config( 'cms.prune' ) ) ) {
            return static::withoutTenancy()->where( 'deleted_at', '<=', now()->subDays( $days ) )
                ->doesntHave( 'versions' )->doesntHave( 'pages' )->doesntHave( 'elements' );
        }

        // pruning is disabled
        return static::withoutTenancy()->where( 'id', '' );
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
