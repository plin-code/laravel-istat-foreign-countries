# Laravel ISTAT Foreign Countries

A Laravel package for importing and managing foreign countries data from ISTAT (Italian National Institute of Statistics).

## Features

- 🌍 Import continents, geographical areas, and foreign countries from ISTAT
- 🔗 Eloquent models with hierarchical relationships
- ⚡ Artisan command for easy data import
- 🔧 Fully configurable via configuration file
- 🆔 UUID primary keys and soft deletes support
- 🏷️ Multiple coding standards support (ISTAT, ISO, MIN, AT)

## Requirements

- PHP 8.3+
- Laravel 12.0+
- league/csv 9.0+
- guzzlehttp/guzzle 7.0+

## Installation

```bash
composer require plin-code/laravel-istat-foreign-countries
```

## Quick Start

1. **Install the package:**
```bash
composer require plin-code/laravel-istat-foreign-countries
```

2. **Publish the configuration:**
```bash
php artisan vendor:publish --provider="PlinCode\IstatForeignCountries\IstatForeignCountriesServiceProvider"
```

3. **Run migrations:**
```bash
php artisan migrate
```

4. **Import the data:**
```bash
php artisan foreign-countries:import
```

That's it! You now have all foreign countries data in your database.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="PlinCode\IstatForeignCountries\IstatForeignCountriesServiceProvider"
```

## Usage

### Data Import

To import all foreign countries data from ISTAT:

```bash
php artisan foreign-countries:import
```

### Models

The package provides three Eloquent models:

#### Continent
```php
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;

$continent = Continent::where('name', 'Europa')->first();
$areas = $continent->areas;
$countries = $continent->countries;
```

#### Area
```php
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Area;

$area = Area::where('name', 'Unione europea')->first();
$countries = $area->countries;
$continent = $area->continent;
```

#### Country
```php
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Country;

// Find by ISO alpha2
$country = Country::where('iso_alpha2', 'FR')->first();

// Find by ISO alpha3
$country = Country::where('iso_alpha3', 'FRA')->first();

// Find by ISTAT code
$country = Country::where('istat_code', '215')->first();

// Access relationships
$continent = $country->continent;
$area = $country->area;

// Check type
if ($country->isState()) {
    echo "This is a state";
}

if ($country->isTerritory()) {
    echo "This is a territory";
    $parent = $country->parentCountry;
}

// Get territories of a country
$france = Country::where('iso_alpha2', 'FR')->first();
$territories = $france->territories;
```

### Facade Usage

```php
use PlinCode\IstatForeignCountries\Facades\IstatForeignCountries;

// Import data programmatically
$count = IstatForeignCountries::import();
```

### Available Codes

Each country includes multiple international coding standards:

- **ISTAT Code**: Italian statistical code
- **ISO Alpha-2**: Two-letter country code (e.g., "FR")
- **ISO Alpha-3**: Three-letter country code (e.g., "FRA")
- **AT Code**: Italian territorial code

## Database Structure

### Continents
- `id` (UUID, primary key)
- `name` (string)
- `istat_code` (string, unique)
- `created_at`, `updated_at`, `deleted_at`

### Areas
- `id` (UUID, primary key)
- `continent_id` (UUID, foreign key)
- `name` (string)
- `istat_code` (string, unique)
- `created_at`, `updated_at`, `deleted_at`

### Countries
- `id` (UUID, primary key)
- `continent_id` (UUID, foreign key)
- `area_id` (UUID, foreign key)
- `parent_country_id` (UUID, foreign key, nullable)
- `type` (string: 'S' for State, 'T' for Territory)
- `name` (string: Italian name)
- `istat_code` (string, unique)
- `iso_alpha2` (string, 2 chars)
- `iso_alpha3` (string, 3 chars)
- `at_code` (string)
- `created_at`, `updated_at`, `deleted_at`

## Relationships

- `Continent` → `hasMany` → `Area`
- `Continent` → `hasMany` → `Country`
- `Area` → `belongsTo` → `Continent`
- `Area` → `hasMany` → `Country`
- `Country` → `belongsTo` → `Continent`
- `Country` → `belongsTo` → `Area`
- `Country` → `belongsTo` → `Country` (parent country, for territories)
- `Country` → `hasMany` → `Country` (territories)

## Configuration

The `config/istat-foreign-countries.php` file allows you to customize:

- **Database connection**: Specify a custom database connection
- **Table names**: Customize the database table names
- **Model classes**: Use your own model classes by extending the base ones
- **CSV URL**: Change the ISTAT data source URL
- **Temporary file name**: Customize the cache file name

### Example Configuration
```php
return [
    'database_connection' => null,

    'tables' => [
        'continents' => 'continents',
        'areas' => 'areas',
        'countries' => 'countries',
    ],

    'models' => [
        'continent' => \App\Models\Continent::class,
        'area' => \App\Models\Area::class,
        'country' => \App\Models\Country::class,
    ],

    'import' => [
        'csv_url' => 'https://www.istat.it/storage/codici-unita-amministrative/Elenco-codici-e-denominazioni-unita-territoriali-estere.csv',
        'temp_filename' => 'istat_foreign_countries.csv',
    ],
];
```

## Testing

Run the test suite:

```bash
composer test
```

The package includes:
- ✅ Unit tests for models and relationships
- ✅ Feature tests for the import service
- ✅ Mocked HTTP requests (no external dependencies)
- ✅ PHPStan static analysis (Level 5)
- ✅ Pest PHP testing framework
- ✅ Architecture tests

### Run Static Analysis
```bash
composer analyse
```

### Code Style Formatting
```bash
composer format
```

## Contributing

1. Fork the project
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Credits

Data source: [ISTAT - Italian National Institute of Statistics](https://www.istat.it/)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
