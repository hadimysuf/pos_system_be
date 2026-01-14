<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->nullOnDelete();

            $table->foreignId('user_id') // pengaju permintaan
                ->constrained('users')
                ->cascadeOnDelete();

            $table->integer('qty_requested');     // jumlah yang diminta
            $table->integer('qty_approved')->nullable(); // yg disetujui

            $table->date('expected_date')->nullable(); // rencana kedatangan

            $table->enum('status', [
                'PENDING',          // diajukan
                'APPROVED',         // disetujui menunggu kirim
                'ON_DELIVERY',      // dalam perjalanan
                'ARRIVED',          // barang sudah masuk stok
                'CANCELLED',        // dibatalkan
                'REJECTED',         // tidak disetujui
            ])->default('PENDING');

            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
