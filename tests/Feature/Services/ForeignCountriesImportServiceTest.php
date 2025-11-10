<?php

use Illuminate\Support\Facades\Http;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Area;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Country;
use PlinCode\IstatForeignCountries\Services\ForeignCountriesImportService;

test('import service imports data from CSV', function () {
    $csvData = "Stato(S)/Territorio(T);Codice Continente;Denominazione Continente (IT);Codice Area;Denominazione Area (IT);Codice ISTAT;Denominazione IT;Denominazione EN;Codice MIN;Codice AT;Codice UNSD_M49;Codice ISO 3166 alpha2;Codice ISO 3166 alpha3;Codice ISTAT_Stato Padre;Codice ISO alpha3_Stato Padre\n";
    $csvData .= "S;1;Europa;11;Unione europea;215;Francia;France;215;Z110;250;FR;FRA;;\n";
    $csvData .= "S;1;Europa;11;Unione europea;216;Germania;Germany;216;Z112;276;DE;DEU;;\n";
    $csvData .= "S;2;Africa;21;Africa settentrionale;301;Algeria;Algeria;301;Z200;012;DZ;DZA;;\n";

    Http::fake([
        '*' => Http::response($csvData, 200),
    ]);

    $service = app(ForeignCountriesImportService::class);
    $count = $service->execute();

    expect($count)->toBe(3)
        ->and(Continent::count())->toBe(2)
        ->and(Area::count())->toBe(2)
        ->and(Country::count())->toBe(3);
});

test('import service handles parent relationships', function () {
    $csvData = "Stato(S)/Territorio(T);Codice Continente;Denominazione Continente (IT);Codice Area;Denominazione Area (IT);Codice ISTAT;Denominazione IT;Denominazione EN;Codice MIN;Codice AT;Codice UNSD_M49;Codice ISO 3166 alpha2;Codice ISO 3166 alpha3;Codice ISTAT_Stato Padre;Codice ISO alpha3_Stato Padre\n";
    $csvData .= "S;1;Europa;11;Unione europea;215;Francia;France;215;Z110;250;FR;FRA;;\n";
    $csvData .= "T;3;America;31;America meridionale;515;Guyana francese;French Guiana;515;Z301;254;GF;GUF;215;FRA\n";

    Http::fake([
        '*' => Http::response($csvData, 200),
    ]);

    $service = app(ForeignCountriesImportService::class);
    $service->execute();

    $territory = Country::where('istat_code', '515')->first();

    expect($territory)->not->toBeNull()
        ->and($territory->parent_country_id)->not->toBeNull()
        ->and($territory->parentCountry->istat_code)->toBe('215');
});
