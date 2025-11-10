<?php

use PlinCode\IstatForeignCountries\Models\ForeignCountries\Area;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Country;

test('area belongs to continent', function () {
    $continent = Continent::factory()->create(['name' => 'Europe']);

    $area = Area::factory()->create([
        'continent_id' => $continent->id,
        'name' => 'European Union',
    ]);

    expect($area->continent)->not->toBeNull()
        ->and($area->continent->name)->toBe('Europe');
});

test('area has many countries', function () {
    $continent = Continent::factory()->create();
    $area = Area::factory()->create(['continent_id' => $continent->id]);

    Country::factory()->count(4)->create([
        'continent_id' => $continent->id,
        'area_id' => $area->id,
    ]);

    expect($area->countries)->toHaveCount(4);
});
