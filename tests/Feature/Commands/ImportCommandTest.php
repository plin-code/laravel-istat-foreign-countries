<?php

use Illuminate\Support\Facades\Http;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Country;

test('import command executes successfully', function () {
    $csvData = "Stato(S)/Territorio(T);Codice Continente;Denominazione Continente (IT);Codice Area;Denominazione Area (IT);Codice ISTAT;Denominazione IT;Denominazione EN;Codice MIN;Codice AT;Codice UNSD_M49;Codice ISO 3166 alpha2;Codice ISO 3166 alpha3;Codice ISTAT_Stato Padre;Codice ISO alpha3_Stato Padre\n";
    $csvData .= "S;1;Europa;11;Unione europea;215;Francia;France;215;Z110;250;FR;FRA;;\n";

    Http::fake([
        '*' => Http::response($csvData, 200),
    ]);

    $this->artisan('foreign-countries:import')
        ->expectsOutput('Starting foreign countries data import...')
        ->assertSuccessful();

    expect(Country::count())->toBeGreaterThan(0);
});
