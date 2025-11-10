<?php

namespace PlinCode\IstatForeignCountries\Models\ForeignCountries;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use PlinCode\IstatForeignCountries\Database\Factories\AreaFactory;

class Area extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'continent_id',
        'name',
        'istat_code',
    ];

    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    protected static function newFactory(): AreaFactory
    {
        return AreaFactory::new();
    }
}
