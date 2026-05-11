<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 20)->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name', 150);
            $table->string('customer_phone', 20)->nullable();
            $table->text('pickup_address')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'picked_up', 'in_process', 'done', 'delivered', 'cancelled'])->default('pending');
            $table->dateTime('pickup_time')->nullable();
            $table->date('estimated_done')->nullable();
            $table->integer('total_price')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->enum('payment_method', ['cash', 'transfer'])->default('cash');
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            $table->integer('discount_amount')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
