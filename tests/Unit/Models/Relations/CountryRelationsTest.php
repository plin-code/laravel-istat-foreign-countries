<?php

use PlinCode\IstatForeignCountries\Models\ForeignCountries\Area;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Country;

test('country belongs to continent', function (): void {
    $continent = Continent::factory()->create(['name' => 'Europe']);
    $area = Area::factory()->create(['continent_id' => $continent->id]);

    $country = Country::factory()->create([
        'continent_id' => $continent->id,
        'area_id' => $area->id,
    ]);

    expect($country->continent)->not->toBeNull()
        ->and($country->continent->name)->toBe('Europe');
});

test('country belongs to area', function (): void {
    $continent = Continent::factory()->create();
    $area = Area::factory()->create([
        'continent_id' => $continent->id,
        'name' => 'European Union',
    ]);

    $country = Country::factory()->create([
        'continent_id' => $continent->id,
        'area_id' => $area->id,
    ]);

    expect($country->area)->not->toBeNull()
        ->and($country->area->name)->toBe('European Union');
});

test('country can have parent country', function (): void {
    $continent = Continent::factory()->create();
    $area = Area::factory()->create(['continent_id' => $continent->id]);

    $parent = Country::factory()->create([
        'continent_id' => $continent->id,
        'area_id' => $area->id,
        'name' => 'Francia',
    ]);

    $territory = Country::factory()->create([
        'continent_id' => $continent->id,
        'area_id' => $area->id,
        'parent_country_id' => $parent->id,
        'name' => 'Guyana Francese',
    ]);

    expect($territory->parentCountry)->not->toBeNull()
        ->and($territory->parentCountry->name)->toBe('Francia');
});

test('country can have territories', function (): void {
    $continent = Continent::factory()->create();
    $area = Area::factory()->create(['continent_id' => $continent->id]);

    $parent = Country::factory()->create([
        'continent_id' => $continent->id,
        'area_id' => $area->id,
        'name' => 'Francia',
    ]);

    Country::factory()->count(3)->create([
        'continent_id' => $continent->id,
        'area_id' => $area->id,
        'parent_country_id' => $parent->id,
    ]);

    expect($parent->territories)->toHaveCount(3);
});
