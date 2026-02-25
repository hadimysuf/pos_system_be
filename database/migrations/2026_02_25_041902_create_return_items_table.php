<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('return_id')
                ->constrained('returns')
                ->onDelete('cascade');

            $table->foreignId('sale_item_id')
                ->constrained('sale_items')
                ->onDelete('cascade');

            $table->integer('quantity');

            $table->decimal('price', 15, 2);
            $table->decimal('cost', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('profit', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
