<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();   // contoh: DASHBOARD, PRODUCTS, SALES
            $table->string('name');             // Nama menu yg tampil
            $table->string('route');            // URL /dashboard /products
            $table->string('icon')->nullable(); // Icon boostrap/tailwind
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
