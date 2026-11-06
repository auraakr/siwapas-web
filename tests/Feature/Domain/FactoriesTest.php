<?php

use App\Domain\Catalog\Models\Facility;
use App\Domain\Payment\Models\Order;
use Illuminate\Database\Eloquent\Model;

$models = collect(glob(__DIR__.'/../../../app/Domain/*/Models/*.php'))
    ->mapWithKeys(fn (string $path) => [
        basename($path, '.php') => 'App\\Domain\\'.basename(dirname($path, 2)).'\\Models\\'.basename($path, '.php'),
    ])
    ->all();

test('every domain model has a working factory', function (string $class) {
    /** @var Model $model */
    $model = $class::factory()->create();

    expect($model->exists)->toBeTrue();
})->with($models);

test('order access token is hidden from serialization', function () {
    $order = Order::factory()->create();

    expect($order->toArray())->not->toHaveKey('access_token');
});

test('translatable columns store both locales', function () {
    $facility = Facility::factory()->create();

    expect($facility->getTranslation('name', 'id'))->toBeString()
        ->and($facility->getTranslation('name', 'en'))->toBeString();
});
