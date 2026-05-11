<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services');
            $table->decimal('quantity', 8, 2);
            $table->integer('unit_price');
            $table->integer('subtotal');
            $table->string('notes', 255)->nullable();
            $table->timestamps(); // Adding timestamps here though PRD didn't explicitly mention it
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
