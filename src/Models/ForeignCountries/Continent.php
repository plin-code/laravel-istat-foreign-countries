<?php

namespace PlinCode\IstatForeignCountries\Models\ForeignCountries;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use PlinCode\IstatForeignCountries\Database\Factories\ContinentFactory;

class Continent extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'istat_code',
    ];

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    protected static function newFactory(): ContinentFactory
    {
        return ContinentFactory::new();
    }
}
