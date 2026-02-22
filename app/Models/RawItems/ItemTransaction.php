<?php

namespace App\Models\RawItems;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormatsDateInputs;
use App\Models\Items\ItemBatchTransaction;
use App\Models\Items\ItemSerialTransaction;
use App\Models\Accounts\AccountTransaction;
use App\Models\Items\Item;
use App\Models\RawItem\Item as RawItems;
use App\Models\Items\ItemStockTransfer;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\Purchase\Purchase;

class ItemTransaction extends Model
{
    use HasFactory;

    use FormatsDateInputs;

    protected $table = 'raw_item_transactions';
    protected $primaryKey = 'id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_type',
        'transaction_id',
        'transaction_date',
        'unique_code',
        'unit_id',
        'raw_item_id',
        'description',
        'unit_price',
        'quantity',
        'tax_id',
        'tax_type',
        'tax_amount',
        'total',
        'discount_amount',
        'discount_type',
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
     * Get the parent transactions model (user or post).
     */
    public function transaction(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * This method calling the Trait FormatsDateInputs
     * @return null or string
     * Use it as formatted_transaction_date
     * */
    public function getFormattedTransactionDateAttribute()
    {
        return $this->toUserDateFormat($this->transaction_date); // Call the trait method
    }
    
    /**
     * ItemTransaction item has unit id
     * 
     * @return BelongsTo
     * */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * ItemTransaction item has item id
     * 
     * @return BelongsTo
     * */
    public function item(): BelongsTo
    {
        return $this->belongsTo(RawItems::class);
    }
}
