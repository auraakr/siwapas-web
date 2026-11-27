<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Posters get a manual order and a publish switch. Existing posters stay visible (is_published
     * defaults to true) and keep their current order: equal sort_order, newest first.
     */
    public function up(): void
    {
        Schema::table('posters', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('alt');
            $table->boolean('is_published')->default(true)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('posters', function (Blueprint $table) {
            $table->dropColumn(['sort_order', 'is_published']);
        });
    }
};
