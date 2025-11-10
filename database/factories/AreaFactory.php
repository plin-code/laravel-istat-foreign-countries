<?php

namespace PlinCode\IstatForeignCountries\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Area;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;

class AreaFactory extends Factory
{
    protected $model = Area::class;

    public function definition(): array
    {
        return [
            'continent_id' => Continent::factory(),
            'name' => fake()->words(2, true),
            'istat_code' => fake()->unique()->numerify('##'),
        ];
    }
}
