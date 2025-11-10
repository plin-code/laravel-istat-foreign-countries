<?php

namespace PlinCode\IstatForeignCountries\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Area;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Country;

class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'continent_id' => Continent::factory(),
            'area_id' => Area::factory(),
            'type' => fake()->randomElement(['S', 'T']),
            'name' => fake()->country(),
            'istat_code' => fake()->unique()->numerify('###'),
            'iso_alpha2' => fake()->countryCode(),
            'iso_alpha3' => strtoupper(fake()->lexify('???')),
            'at_code' => fake()->bothify('Z###'),
        ];
    }

    public function asState(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'S',
        ]);
    }

    public function asTerritory(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'T',
        ]);
    }
}
