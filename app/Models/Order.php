<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $guarded = ['id'];

    public function product(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot('count');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if($customer = $filters['customer'] ?? null) {
            $query->where('customer', 'like', '%'.$customer.'%');
        }

        if($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        if($completedAt = $filters['completed_at'] ?? null) {
            $startOfDay = \Carbon\Carbon::parse($completedAt)->startOfDay();
            $endOfDay = \Carbon\Carbon::parse($completedAt)->endOfDay();
            $query->whereBetween('completed_at', [$startOfDay, $endOfDay]);
        }

        if($warehouseId = $filters['warehouse_id'] ?? null) {
            $query->where('warehouse_id', $warehouseId);
        }
        return $query;
    }
}
