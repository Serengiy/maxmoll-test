<?php

use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Stock::class)->index('idx_stock_movements_stock_id');
            $table->foreignIdFor(Product::class)->index('idx_stock_movements_product_id');
            $table->foreignIdFor(Warehouse::class)->index('idx_stock_movements_warehouse');
            $table->timestamp('changed_at')->index('idx_stock_movements_changed_at');
            $table->string('type', 20)->index('idx_stock_movements_type');
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
