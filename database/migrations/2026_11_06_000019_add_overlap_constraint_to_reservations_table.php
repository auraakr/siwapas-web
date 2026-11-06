<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');

        DB::statement('ALTER TABLE reservations ADD CONSTRAINT reservations_check_out_after_check_in CHECK (check_out > check_in)');

        DB::statement(<<<'SQL'
            ALTER TABLE reservations
            ADD CONSTRAINT reservations_no_overlap
            EXCLUDE USING gist (facility_id WITH =, daterange(check_in, check_out) WITH &&)
            WHERE (is_active)
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE reservations DROP CONSTRAINT IF EXISTS reservations_no_overlap');
        DB::statement('ALTER TABLE reservations DROP CONSTRAINT IF EXISTS reservations_check_out_after_check_in');
    }
};
