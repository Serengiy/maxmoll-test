<?php

namespace App\Observers;

use App\Models\Stock;
use App\Models\StockMovement;

class StockObserver
{
    /**
     * Handle the Stock "created" event.
     */
    public function created(Stock $stock): void
    {
        StockMovement::create([
            'product_id'   => $stock->product_id,
            'stock_id'     => $stock->stock,
            'changed_at'   => now(),
            'warehouse_id' => $stock->warehouse_id,
            'type'         => StockMovement::CREATED_TYPE,
            'description'  => "Новый товар на складе в количестве {$stock->stock}",
        ]);
    }

    /**
     * Handle the Stock "updated" event.
     */
    public function updated(Stock $stock): void
    {
        $changes = $stock->getChanges();

        if (array_key_exists('stock', $changes)) {
            $originalStock = $stock->getOriginal('stock');
            StockMovement::create([
                'product_id'  => $stock->product_id,
                'stock_id'    => $stock->stock,
                'changed_at'  => now(),
                'warehouse_id'   => $stock->warehouse_id,
                'type'        => StockMovement::UPDATED_TYPE,
                'description' => "Остатки обновлены с {$originalStock} до {$stock->stock}.",
            ]);
        } else {
            $fieldsChanged = implode(', ', array_keys($changes));
            StockMovement::create([
                'product_id'  => $stock->product_id,
                'stock_id'    => $stock->stock,
                'changed_at'  => now(),
                'warehouse_id'   => $stock->warehouse_id,
                'type'        => StockMovement::UPDATED_TYPE,
                'description' => "Обновлены поля остатков: {$fieldsChanged}.",
            ]);
        }
    }

    /**
     * Handle the Stock "deleted" event.
     */
    public function deleted(Stock $stock): void
    {
        StockMovement::create([
            'product_id'  => $stock->product_id,
            'stock_id'    => $stock->stock,
            'changed_at'  => now(),
            'warehouse_id'   => $stock->warehouse_id,
            'type'        => StockMovement::DELETED_TYPE,
            'description' => "Остатки удалены в количестве: {$stock->stock}.",
        ]);
    }

    /**
     * Handle the Stock "restored" event.
     */
    public function restored(Stock $stock): void
    {
        //
    }

    /**
     * Handle the Stock "force deleted" event.
     */
    public function forceDeleted(Stock $stock): void
    {
        //
    }
}
