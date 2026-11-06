<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_products', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('maker_name')->nullable();
            $table->json('description')->nullable();
            $table->string('whatsapp_number', 30)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_products');
    }
};
