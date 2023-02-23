<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * Page content model
 */
class Content extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Prunable;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_contents';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'data' => '[]',
        'status' => 0,
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
     * Get the page for the content.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo( Page::class );
    }


    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where( 'deleted_at', '<=', now()->subDays( config( 'cms.prune', 30 ) ) );
    }
}
