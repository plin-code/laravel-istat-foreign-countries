<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | The database connection to use for the ISTAT foreign countries tables.
    | If null, the default connection will be used.
    |
    */

    'database_connection' => null,

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | The names of the database tables used by this package.
    |
    */

    'tables' => [
        'continents' => 'continents',
        'areas' => 'areas',
        'countries' => 'countries',
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Classes
    |--------------------------------------------------------------------------
    |
    | The model classes used by this package. You can extend these models
    | and specify your custom classes here.
    |
    */

    'models' => [
        'continent' => \PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent::class,
        'area' => \PlinCode\IstatForeignCountries\Models\ForeignCountries\Area::class,
        'country' => \PlinCode\IstatForeignCountries\Models\ForeignCountries\Country::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Import Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the CSV import process.
    |
    */

    'import' => [
        'csv_url' => 'https://www.istat.it/storage/codici-unita-amministrative/Elenco-codici-e-denominazioni-unita-territoriali-estere.csv',
        'temp_filename' => 'istat_foreign_countries.csv',
    ],

];
