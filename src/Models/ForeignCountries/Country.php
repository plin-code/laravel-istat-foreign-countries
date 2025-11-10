<?php

namespace PlinCode\IstatForeignCountries\Models\ForeignCountries;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use PlinCode\IstatForeignCountries\Database\Factories\CountryFactory;

class Country extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'continent_id',
        'area_id',
        'parent_country_id',
        'type',
        'name',
        'istat_code',
        'iso_alpha2',
        'iso_alpha3',
        'at_code',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function parentCountry(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'parent_country_id');
    }

    public function territories(): HasMany
    {
        return $this->hasMany(__CLASS__, 'parent_country_id');
    }

    public function isState(): bool
    {
        return $this->type === 'S';
    }

    public function isTerritory(): bool
    {
        return $this->type === 'T';
    }

    protected static function newFactory(): CountryFactory
    {
        return CountryFactory::new();
    }
}
