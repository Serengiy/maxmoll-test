<?php

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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('customer', 120)->index('idx_orders_customer_name');
            $table->dateTime('completed_at')->nullable()->index('idx_orders_completed_at');
            $table->foreignIdFor(Warehouse::class)->index('idx_orders_warehouse_id');
            $table->string('status', 20)->index('idx_orders_status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
