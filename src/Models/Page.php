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
use Kalnoy\Nestedset\NodeTrait;


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
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_pages';


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
        $rel = $this->hasOne( Content::class )->orderBy( 'id', 'desc' );
        return ( $cid = request()->input( 'cid' ) ) ? $rel->where( 'id', $cid ) : $rel->where( 'status', '>', 0 );
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
        $root = DB::table( 'cms_pages' )->where( 'tag', $tag )->where( 'lang', $lang )->first();
        return $root ? Page::descendantsAndSelf( $root->id )->toTree()->first() : null;
    }


    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where( 'deleted_at', '<=', now()->subDays( config( 'cms.prune', 30 ) ) );
    }
}
