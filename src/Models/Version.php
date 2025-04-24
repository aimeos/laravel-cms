<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Models;

use Aimeos\Cms\Concerns\Tenancy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * Version model
 */
class Version extends Model
{
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
        'contents' => null,
        'publish_at' => null,
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
        'contents' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'publish_at',
        'contents',
        'editor',
        'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_versions';


    /**
     * Get all files referenced by the versioned data.
     */
    public function files() : BelongsToMany
    {
        return $this->belongsToMany( File::class, 'cms_file_version' );
    }


    /**
     * Get the connection name for the model.
     */
    public function getConnectionName()
    {
        return config( 'cms.db', 'sqlite' );
    }


    /**
     * Get the shared element attached to the version.
     */
    public function elements() : BelongsToMany
    {
        return $this->belongsToMany( Element::class, 'cms_version_element' );
    }


    /**
     * Get the parent versionable model (page or element).
     */
    public function versionable() : MorphTo
    {
        return $this->morphTo();
    }
}
