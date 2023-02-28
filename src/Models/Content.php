<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * Page content model
 */
class Content extends Model
{
    use HasUuids;
    use HasFactory;
    use SoftDeletes;
    use MassPrunable;


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
    protected $table = 'cms_contents';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'page_id' => null,
        'data' => '[]',
        'status' => 0,
        'editor' => '',
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
        'page_id',
        'data',
    ];


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
     * Generate a new UUID for the model.
     */
    public function newUniqueId(): string
    {
        return (string) new \Symfony\Component\Uid\UuidV7();
    }


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
        if( is_int( $days = config( 'cms.prune' ) ) ) {
            return static::where( 'deleted_at', '<=', now()->subDays( $days ) );
        }

        // pruning is disabled
        return static::where( 'id', '' );
    }
}
