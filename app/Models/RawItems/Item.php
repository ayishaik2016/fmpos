<?php

namespace App\Models\RawItems;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use App\Models\Tax;
use App\Models\User;
use App\Models\Unit;
use App\Models\Party\CustomerItem;
use App\Models\RawItems\ItemCategory;
use App\Models\RawItems\ItemTransaction;
use App\Models\Items\ItemGeneralQuantity;

class Item extends Model
{
    use HasFactory;
    
    protected $table = 'raw_items';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id', 'prefix_code', 'count_id', 'item_code', 'name', 'description', 'hsn', 'sku', 'item_category_id', 'base_unit_id', 'price', 'is_sale_price_with_tax', 'tax_id', 'image_path', 'status', 'created_by', 'updated_by', 'current_stock'
    ];
    /**
     * Insert & update User Id's
     * */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }

    /**
     * Get the tax associated with the service.
     *
     * @return BelongsTo
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * Define the relationship between Order and User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Define the relationship between Item and Unit.
     *
     * @return BelongsTo
     * */
    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /**
     * Define the relationship between Item and Unit.
     *
     * @return BelongsTo
     * */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    /**
     * Define the relationship between Item Transaction & Items table.
     *
     * @return MorphMany
     */
    public function itemTransaction(): MorphMany
    {
        return $this->morphMany(ItemTransaction::class, 'transaction');
    }

}
