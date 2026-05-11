<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('service_categories')->onDelete('cascade');
            $table->string('name', 150);
            $table->enum('unit', ['kg', 'pcs']);
            $table->integer('duration_days')->nullable();
            $table->string('duration_label', 50)->nullable();
            $table->enum('difficulty', ['normal', 'hard', 'sexy'])->default('normal');
            $table->enum('size', ['S', 'M', 'L', 'XL'])->nullable();
            $table->integer('price_min');
            $table->integer('price_max')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
