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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * Element model
 */
class Element extends Model
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
        'type' => '',
        'lang' => '',
        'label' => '',
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
        'label' => 'string',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'lang',
        'label',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_elements';


    /**
     * Get the files referencedd by the element.
     */
    public function files() : BelongsToMany
    {
        return $this->belongsToMany( File::class, 'cms_file_element' );
    }


    /**
     * Get the connection name for the model.
     */
    public function getConnectionName()
    {
        return config( 'cms.db', 'sqlite' );
    }


    /**
     * Get the page's latest head/meta data.
     */
    public function latest() : HasOne
    {
        return $this->hasOne( Version::class, 'versionable_id' )
            ->where( 'versionable_type', Element::class )
            ->orderBy( 'id', 'desc' )
            ->take( 1 );
    }


    /**
     * Get the pages the element is referenced by.
     */
    public function pages() : BelongsToMany
    {
        return $this->belongsToMany( Page::class, 'cms_page_element' );
    }


    /**
     * Get the element's published version.
     */
    public function published() : HasOne
    {
        return $this->hasOne( Version::class, 'versionable_id' )
            ->where( 'versionable_type', Element::class )
            ->where( 'published', true )
            ->orderBy( 'id', 'desc' )
            ->take( 1 );
    }


    /**
     * Get the prunable model query.
     */
    public function prunable() : Builder
    {
        return static::withoutTenancy()->where( 'deleted_at', '<=', now()->subDays( config( 'cms.prune', 30 ) ) );
    }


    /**
     * Get all of the element's versions.
     */
    public function versions() : MorphMany
    {
        return $this->morphMany( Version::class, 'versionable' );
    }
}
