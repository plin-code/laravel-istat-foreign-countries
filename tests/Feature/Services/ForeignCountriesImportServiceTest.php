<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Area;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Country;
use PlinCode\IstatForeignCountries\Services\ForeignCountriesImportService;

uses(RefreshDatabase::class);

test('import service imports data from CSV', function (): void {
    $csvData = "Stato(S)/Territorio(T);Codice Continente;Denominazione Continente (IT);Codice Area;Denominazione Area (IT);Codice ISTAT;Denominazione IT;Denominazione EN;Codice MIN;Codice AT;Codice UNSD_M49;Codice ISO 3166 alpha2;Codice ISO 3166 alpha3;Codice ISTAT_Stato Padre;Codice ISO alpha3_Stato Padre\n";
    $csvData .= "S;1;Europa;11;Unione europea;215;Francia;France;215;Z110;250;FR;FRA;;\n";
    $csvData .= "S;1;Europa;11;Unione europea;216;Germania;Germany;216;Z112;276;DE;DEU;;\n";
    $csvData .= "S;2;Africa;21;Africa settentrionale;301;Algeria;Algeria;301;Z200;012;DZ;DZA;;\n";

    Http::fake([
        '*' => Http::response($csvData, 200),
    ]);

    $service = app(ForeignCountriesImportService::class);

    // Debug: controlla cosa viene effettivamente parsato
    try {
        $count = $service->execute();
    } catch (\Exception $e) {
        dump('Error during import: ' . $e->getMessage());
        throw $e;
    }

    // Debug dettagliato
    dump([
        'Total count returned' => $count,
        'Countries in DB' => Country::count(),
        'Country codes' => Country::pluck('istat_code')->toArray(),
        'Continents' => Continent::count(),
        'Areas' => Area::count(),
    ]);

    expect(Country::count())->toBeGreaterThanOrEqual(2)
        ->and(Continent::count())->toBe(2)
        ->and(Area::count())->toBe(2);

    expect(Country::where('istat_code', '215')->exists())->toBeTrue();
});
