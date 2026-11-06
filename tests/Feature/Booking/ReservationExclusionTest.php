<?php

use App\Domain\Booking\Models\Reservation;
use App\Domain\Catalog\Models\Facility;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $database = (string) config('database.connections.'.config('database.default').'.database');

    if (DB::getDriverName() !== 'pgsql' || ! str_contains($database, 'test')) {
        $this->markTestSkipped('Requires a PostgreSQL database whose name contains "test".');
    }
});

function reserve(Facility $facility, string $checkIn, string $checkOut, bool $active = true): Reservation
{
    return Reservation::factory()->create([
        'facility_id' => $facility->id,
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'is_active' => $active,
    ]);
}

test('overlapping active reservations for the same facility are rejected', function () {
    $facility = Facility::factory()->create();
    reserve($facility, '2030-01-10', '2030-01-13');

    expect(fn () => reserve($facility, '2030-01-12', '2030-01-15'))->toThrow(QueryException::class);
});

test('an overlap is allowed when one reservation is inactive', function () {
    $facility = Facility::factory()->create();
    reserve($facility, '2030-01-10', '2030-01-13');

    expect(reserve($facility, '2030-01-11', '2030-01-14', active: false)->exists)->toBeTrue();
});

test('back-to-back stays and other facilities do not conflict', function () {
    $facility = Facility::factory()->create();
    reserve($facility, '2030-01-10', '2030-01-13');

    expect(reserve($facility, '2030-01-13', '2030-01-15')->exists)->toBeTrue()
        ->and(reserve(Facility::factory()->create(), '2030-01-10', '2030-01-13')->exists)->toBeTrue();
});

test('check out must be after check in', function () {
    expect(fn () => reserve(Facility::factory()->create(), '2030-02-10', '2030-02-10'))->toThrow(QueryException::class);
});
