<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class StockMovement extends Model
{
    const CREATED_TYPE = 'created';
    const UPDATED_TYPE = 'updated';
    const DELETED_TYPE = 'deleted';
    protected $guarded = ['id'];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function scopeFilter(Builder $builder, array $filters)
    {
        if($productId = $filters['product_id'] ?? null) {
            $builder->where('product_id', $productId);
        }

        if($warehouseId = $filters['warehouse_id'] ?? null) {
            $builder->where('warehouse_id', $warehouseId);
        }

        if($type = $filters['type'] ?? null) {
            $builder->where('type', $type);
        }

        if($from = $filters['from'] ?? null) {
            $builder->where('changed_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if($to = $filters['to'] ?? null) {
            $builder->where('changed_at', '<=', Carbon::parse($to)->endOfDay());
        }

        if($description = $filters['description'] ?? null) {
            $builder->where('description', 'like', '%'.$description.'%');
        }

        if ($day = $filters['day'] ?? null) {
            $startOfDay = \Carbon\Carbon::parse($day)->startOfDay();
            $endOfDay = \Carbon\Carbon::parse($day)->endOfDay();

            $builder->whereBetween('changed_at', [$startOfDay, $endOfDay]);
        }

        return $builder;
    }
}
