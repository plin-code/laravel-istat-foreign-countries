<?php

use PlinCode\IstatForeignCountries\Models\ForeignCountries\Area;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;

it('can create an area', function () {
    $continent = Continent::factory()->create();

    $area = Area::factory()->create([
        'continent_id' => $continent->id,
        'name' => 'European Union',
        'istat_code' => '11',
    ]);

    expect($area->name)->toBe('European Union')
        ->and($area->istat_code)->toBe('11')
        ->and($area->continent_id)->toBe($continent->id);
});

it('can retrieve an area from database', function () {
    $area = Area::factory()->create([
        'name' => 'Eastern Europe',
        'istat_code' => '12',
    ]);

    $retrieved = Area::find($area->id);

    expect($retrieved)->not->toBeNull()
        ->and($retrieved->name)->toBe('Eastern Europe')
        ->and($retrieved->istat_code)->toBe('12');
});
