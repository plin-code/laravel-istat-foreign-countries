<?php

use PlinCode\IstatForeignCountries\Models\ForeignCountries\Area;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Country;

it('can create a country', function () {
    $continent = Continent::factory()->create();
    $area = Area::factory()->create(['continent_id' => $continent->id]);

    $country = Country::factory()->create([
        'continent_id' => $continent->id,
        'area_id' => $area->id,
        'type' => 'S',
        'name_it' => 'Francia',
        'name_en' => 'France',
        'istat_code' => '215',
        'iso_alpha2' => 'FR',
        'iso_alpha3' => 'FRA',
    ]);

    expect($country->name_it)->toBe('Francia')
        ->and($country->name_en)->toBe('France')
        ->and($country->istat_code)->toBe('215')
        ->and($country->iso_alpha2)->toBe('FR')
        ->and($country->iso_alpha3)->toBe('FRA')
        ->and($country->type)->toBe('S');
});

it('can distinguish between state and territory', function () {
    $state = Country::factory()->state()->create();
    $territory = Country::factory()->territory()->create();

    expect($state->isState())->toBeTrue()
        ->and($state->isTerritory())->toBeFalse()
        ->and($territory->isState())->toBeFalse()
        ->and($territory->isTerritory())->toBeTrue();
});

it('can have a parent country', function () {
    $parent = Country::factory()->state()->create([
        'name_it' => 'Francia',
    ]);

    $territory = Country::factory()->territory()->create([
        'parent_country_id' => $parent->id,
        'name_it' => 'Guyana Francese',
    ]);

    expect($territory->parent_country_id)->toBe($parent->id)
        ->and($territory->parentCountry->name_it)->toBe('Francia');
});
