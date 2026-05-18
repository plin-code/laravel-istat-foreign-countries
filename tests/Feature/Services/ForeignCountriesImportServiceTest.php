<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Area;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Continent;
use PlinCode\IstatForeignCountries\Models\ForeignCountries\Country;
use PlinCode\IstatForeignCountries\Services\ForeignCountriesImportService;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $storage = Storage::disk('local');
    $filename = config('istat-foreign-countries.import.temp_filename');
    if ($storage->exists($filename)) {
        $storage->delete($filename);
    }
});

test('import service imports data from CSV', function (): void {
    $csvData = "Stato(S)/Territorio(T);Codice Continente;Denominazione Continente (IT);Codice Area;Denominazione Area (IT);Codice ISTAT;Denominazione IT;Denominazione EN;Codice MIN;Codice AT;Codice UNSD_M49;Codice ISO 3166 alpha2;Codice ISO 3166 alpha3;Codice ISTAT_Stato Padre;Codice ISO alpha3_Stato Padre\n";
    $csvData .= "S;1;Europa;11;Unione europea;215;Francia;France;215;Z110;250;FR;FRA;;\n";
    $csvData .= "S;1;Europa;11;Unione europea;216;Germania;Germany;216;Z112;276;DE;DEU;;\n";
    $csvData .= "S;2;Africa;21;Africa settentrionale;301;Algeria;Algeria;301;Z200;012;DZ;DZA;;\n";

    Http::fake([
        '*' => Http::response($csvData, 200),
    ]);

    $service = app(ForeignCountriesImportService::class);

    $count = $service->execute();

    expect(Country::count())->toBeGreaterThanOrEqual(2)
        ->and(Continent::count())->toBe(2)
        ->and(Area::count())->toBe(2);

    expect(Country::where('istat_code', '215')->exists())->toBeTrue();
});

test('import service correctly sanitizes various placeholders into null values', function (): void {
    $csvData = "Stato(S)/Territorio(T);Codice Continente;Denominazione Continente (IT);Codice Area;Denominazione Area (IT);Codice ISTAT;Denominazione IT;Denominazione EN;Codice MIN;Codice AT;Codice UNSD_M49;Codice ISO 3166 alpha2;Codice ISO 3166 alpha3;Codice ISTAT_Stato Padre;Codice ISO alpha3_Stato Padre\n";
    $csvData .= "S;1;Europa;11;Unione europea;215;Francia;France;215;Z110;250;FR;FRA;;\n";
    $csvData .= "S;1;Europa;13;Altri paesi europei;219;Regno Unito;United Kingdom;219;Z114;826;UK;GBR;;\n";
    $csvData .= "S;5;Oceania;50;Oceania;721;Papua Nuova Guinea;Papua New Guinea;721;Z730;598;PG;PNG;;\n";
    $csvData .= "S;1;Europa;12;Europa centro orientale;272;Kosovo;Kosovo;272;Z160;n.d.;N.d.;KOS;;\n";
    $csvData .= "T;4;America;42;America centro meridionale;904;Saint-Martin (FR);Saint Martin (FR);542;n.d.;n.D.;MF;MAF;215;FRA\n";
    $csvData .= "T;5;Oceania;50;Oceania;988;Terre australi e antartiche francesi;French Southern Territories;806;n.d.;N.D.;TF;ATF;215;FRA\n";
    $csvData .= "S;1;Europa;13;Altri Paesi europei;939;Sark;Sark;n.d.;null;n.d.;;-;219;GBR\n";

    Http::fake([
        '*' => Http::response($csvData, 200),
    ]);

    $service = app(ForeignCountriesImportService::class);
    $service->execute();

    $papua = Country::where('istat_code', '721')->first();
    expect($papua)->not->toBeNull()
        ->and($papua->at_code)->toBe('Z730')
        ->and($papua->iso_alpha2)->toBe('PG')
        ->and($papua->iso_alpha3)->toBe('PNG')
        ->and($papua->parent_country_id)->toBeNull();

    $kosovo = Country::where('istat_code', '272')->first();
    expect($kosovo)->not->toBeNull()
        ->and($kosovo->at_code)->toBe('Z160')
        ->and($kosovo->iso_alpha2)->toBeNull()
        ->and($kosovo->iso_alpha3)->toBe('KOS')
        ->and($papua->parent_country_id)->toBeNull();

    $saintMartin = Country::where('istat_code', '904')->first();
    expect($saintMartin)->not->toBeNull()
        ->and($saintMartin->at_code)->toBeNull()
        ->and($saintMartin->iso_alpha2)->toBe('MF')
        ->and($saintMartin->iso_alpha3)->toBe('MAF')
        ->and($saintMartin->parent_country_id)->not->toBeNull();

    $terreAustrali = Country::where('istat_code', '988')->first();
    expect($terreAustrali)->not->toBeNull()
        ->and($terreAustrali->at_code)->toBeNull()
        ->and($terreAustrali->iso_alpha2)->toBe('TF')
        ->and($terreAustrali->iso_alpha3)->toBe('ATF')
        ->and($terreAustrali->parent_country_id)->not->toBeNull();

    $sark = Country::where('istat_code', '939')->first();
    expect($sark)->not->toBeNull()
        ->and($sark->at_code)->toBeNull()
        ->and($sark->iso_alpha2)->toBeNull()
        ->and($sark->iso_alpha3)->toBeNull()
        ->and($sark->parent_country_id)->not->toBeNull();
});
