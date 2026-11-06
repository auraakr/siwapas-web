<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('participant_count')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('registration_code')->unique();

            $table->index(['event_id', 'is_active']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
