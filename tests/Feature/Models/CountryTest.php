<?php

use PlinCode\IstatForeignCountries\Models\ForeignCountries\Area;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Country;

it('can create a country', function (): void {
    $continent = Continent::factory()->create();
    $area = Area::factory()->create(['continent_id' => $continent->id]);

    $country = Country::factory()->create([
        'continent_id' => $continent->id,
        'area_id' => $area->id,
        'type' => 'S',
        'name' => 'Francia',
        'istat_code' => '215',
        'iso_alpha2' => 'FR',
        'iso_alpha3' => 'FRA',
    ]);

    expect($country->name)->toBe('Francia')
        ->and($country->istat_code)->toBe('215')
        ->and($country->iso_alpha2)->toBe('FR')
        ->and($country->iso_alpha3)->toBe('FRA')
        ->and($country->type)->toBe('S');
});

it('can distinguish between state and territory', function (): void {
    $state = Country::factory()->asState()->create();
    $territory = Country::factory()->asTerritory()->create();

    expect($state->isState())->toBeTrue()
        ->and($state->isTerritory())->toBeFalse()
        ->and($territory->isState())->toBeFalse()
        ->and($territory->isTerritory())->toBeTrue();
});

it('can have a parent country', function (): void {
    $parent = Country::factory()->asState()->create([
        'name' => 'Francia',
    ]);

    $territory = Country::factory()->asTerritory()->create([
        'parent_country_id' => $parent->id,
        'name' => 'Guyana Francese',
    ]);

    expect($territory->parent_country_id)->toBe($parent->id)
        ->and($territory->parentCountry->name)->toBe('Francia');
});
