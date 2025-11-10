<?php

use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;

it('can create a continent', function (): void {
    $continent = Continent::factory()->create([
        'name' => 'Europe',
        'istat_code' => '1',
    ]);

    expect($continent->name)->toBe('Europe')
        ->and($continent->istat_code)->toBe('1')
        ->and($continent->id)->not->toBeNull();
});

it('can retrieve a continent from database', function (): void {
    $continent = Continent::factory()->create([
        'name' => 'Asia',
        'istat_code' => '2',
    ]);

    $retrieved = Continent::find($continent->id);

    expect($retrieved)->not->toBeNull()
        ->and($retrieved->name)->toBe('Asia')
        ->and($retrieved->istat_code)->toBe('2');
});
