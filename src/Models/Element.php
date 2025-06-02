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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


/**
 * Element model
 */
class Element extends Model
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
        'type' => '',
        'lang' => '',
        'name' => '',
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
        'name' => 'string',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data',
        'type',
        'lang',
        'name',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_elements';


    /**
     * Get the pages the element is referenced by.
     *
     * @return BelongsToMany Eloquent relationship to the pages
     */
    public function bypages() : BelongsToMany
    {
        return $this->belongsToMany( Page::class, 'cms_page_element' )
            ->select('id', 'slug', 'name' );
    }


    /**
     * Get the versions the element is referenced by.
     *
     * @return BelongsToMany Eloquent relationship to the versions referencing the element
     */
    public function byversions() : BelongsToMany
    {
        return $this->belongsToMany( Version::class, 'cms_version_element' )
            ->select('id', 'versionable_id', 'versionable_type', 'published', 'publish_at' );
    }


    /**
     * Get the files referencedd by the element.
     *
     * @return BelongsToMany Eloquent relationship to the files
     */
    public function files() : BelongsToMany
    {
        return $this->belongsToMany( File::class, 'cms_element_file' );
    }


    /**
     * Get the connection name for the model.
     *
     * @return string Name of the database connection to use
     */
    public function getConnectionName() : string
    {
        return config( 'cms.db', 'sqlite' );
    }


    /**
     * Get the page's latest head/meta data.
     *
     * @return HasOne Eloquent relationship to the latest version of the element
     */
    public function latest() : HasOne
    {
        return $this->hasOne( Version::class, 'versionable_id' )
            ->where( 'versionable_type', Element::class )
            ->orderBy( 'id', 'desc' )
            ->take( 1 );
    }


    /**
     * Publish the given version of the element.
     *
     * @param Version $version Version to publish
     * @return self Returns the element instance
     */
    public function publish( Version $version ) : self
    {
        DB::connection( $this->getConnectionName() )->transaction( function() use ( $version ) {

            $this->files()->sync( $version->files ?? [] );

            $this->fill( (array) $version->data );
            $this->editor = $version->editor;
            $this->lang = $version->lang;
            $this->save();

            $version->published = true;
            $version->save();

        }, 3 );

        return $this;
    }


    /**
     * Get the element's published version.
     *
     * @return HasOne Eloquent relationship to the last published version of the element
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
     *
     * @return Builder Eloquent query builder instance for pruning
     */
    public function prunable() : Builder
    {
        return static::withoutTenancy()->where( 'deleted_at', '<=', now()->subDays( config( 'cms.prune', 30 ) ) );
    }


    /**
     * Get all of the element's versions.
     *
     * @return MorphMany Eloquent relationship to the versions of the element
     */
    public function versions() : MorphMany
    {
        return $this->morphMany( Version::class, 'versionable' );
    }


    /**
     * Prepare the model for pruning.
     */
    protected function pruning() : void
    {
        Version::where( 'versionable_id', $this->id )
            ->where( 'versionable_type', Element::class )
            ->delete();
    }
}
