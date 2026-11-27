<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reservations are paid with a down payment (DP) and a settlement. Every new column is nullable or
     * has a default, so existing orders keep working: an order without dp_percentage is paid in full,
     * and every existing payment covered its whole order.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedTinyInteger('dp_percentage')->nullable()->after('total');
            $table->unsignedInteger('dp_amount')->nullable()->after('dp_percentage');
            $table->unsignedInteger('remaining_amount')->nullable()->after('dp_amount');
            $table->string('settlement_method')->nullable()->after('remaining_amount');
            $table->timestamp('settled_at')->nullable()->after('settlement_method');
            $table->string('cancelled_by')->nullable()->after('settled_at');
            $table->text('resolution_note')->nullable()->after('cancelled_by');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('type')->default('full')->after('order_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'dp_percentage',
                'dp_amount',
                'remaining_amount',
                'settlement_method',
                'settled_at',
                'cancelled_by',
                'resolution_note',
            ]);
        });
    }
};
