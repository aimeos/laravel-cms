<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 */


namespace Aimeos\Cms\Models;

use Aimeos\Cms\Concerns\Tenancy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * Page<->content reference model
 */
class Ref extends Model
{
    use HasUuids;
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
    protected $table = 'cms_page_content';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'tenant_id' => '',
        'position' => 0,
        'status' => 0,
        'editor' => '',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'page_id',
        'content_id',
        'position',
        'status',
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
}
