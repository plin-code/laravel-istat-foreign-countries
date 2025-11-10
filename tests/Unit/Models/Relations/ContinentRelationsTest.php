<?php

use PlinCode\IstatForeignCountries\Models\ForeignCountries\Area;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Country;

test('continent has many areas', function () {
    $continent = Continent::factory()->create();

    Area::factory()->count(3)->create([
        'continent_id' => $continent->id,
    ]);

    expect($continent->areas)->toHaveCount(3);
});

test('continent has many countries', function () {
    $continent = Continent::factory()->create();
    $area = Area::factory()->create(['continent_id' => $continent->id]);

    Country::factory()->count(5)->create([
        'continent_id' => $continent->id,
        'area_id' => $area->id,
    ]);

    expect($continent->countries)->toHaveCount(5);
});
