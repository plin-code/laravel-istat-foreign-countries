<?php

namespace PlinCode\IstatForeignCountries;

use PlinCode\IstatForeignCountries\Commands\IstatForeignCountriesCommand;
use PlinCode\IstatForeignCountries\Services\ForeignCountriesImportService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class IstatForeignCountriesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-istat-foreign-countries')
            ->hasConfigFile('istat-foreign-countries')
            ->hasViews()
            ->hasMigration('create_istat_foreign_countries_table')
            ->hasCommand(IstatForeignCountriesCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('istat-foreign-countries', function ($app) {
            return new IstatForeignCountries(
                $app->make(ForeignCountriesImportService::class)
            );
        });

        $this->app->singleton(ForeignCountriesImportService::class);
    }
}
