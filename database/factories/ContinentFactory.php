<?php

namespace PlinCode\IstatForeignCountries\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;

class ContinentFactory extends Factory
{
    protected $model = Continent::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'istat_code' => fake()->unique()->numerify('##'),
        ];
    }
}
