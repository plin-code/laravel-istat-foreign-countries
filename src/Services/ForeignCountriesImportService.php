<?php

namespace PlinCode\IstatForeignCountries\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use League\Csv\Bom;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\UnavailableFeature;
use League\Csv\UnavailableStream;

class ForeignCountriesImportService
{
    private string $csvUrl;

    private string $tempFilename;

    private ?string $connection;

    private string $continentModel;

    private string $areaModel;

    private string $countryModel;

    public function __construct()
    {
        $this->csvUrl = config('istat-foreign-countries.import.csv_url');
        $this->tempFilename = config('istat-foreign-countries.import.temp_filename');
        $this->connection = config('istat-foreign-countries.database_connection');
        $this->continentModel = config('istat-foreign-countries.models.continent');
        $this->areaModel = config('istat-foreign-countries.models.area');
        $this->countryModel = config('istat-foreign-countries.models.country');
    }

    /**
     * @throws \Exception
     */
    public function execute(): int
    {
        $csvPath = $this->downloadCsv();
        $csv = $this->prepareCsvReader($csvPath);

        return $this->processRecords($csv);
    }

    private function downloadCsv(): string
    {
        $storage = Storage::disk('local');

        if ($storage->exists($this->tempFilename)) {
            $filePath = $storage->path($this->tempFilename);
            if (file_exists($filePath)) {
                $lastModified = filemtime($filePath);
                if ($lastModified && date('Y-m-d') === date('Y-m-d', $lastModified)) {
                    return $filePath;
                }
            }
        }

        $response = Http::timeout(60)->get($this->csvUrl);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to download CSV from ISTAT');
        }

        $storage->put($this->tempFilename, $response->body());

        return $storage->path($this->tempFilename);
    }

    /**
     * @throws InvalidArgument
     * @throws UnavailableStream
     * @throws UnavailableFeature
     * @throws Exception
     */
    private function prepareCsvReader(string $path): Reader
    {
        $csv = Reader::from($path, 'r');
        $csv->setOutputBOM(Bom::Utf8);
        $csv->appendStreamFilterOnRead('convert.iconv.ISO-8859-15/UTF-8');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    /**
     * @throws Exception
     */
    private function processRecords(Reader $csv): int
    {
        $records = $csv->getRecords();
        $count = 0;

        $continents = [];
        $areas = [];
        $countriesByIstatCode = [];

        // First pass: create continents, areas and countries
        foreach ($records as $record) {
            $values = array_values($record);

            if (count($values) < 13) {
                continue;
            }

            $type = $values[0];
            $continentCode = $values[1];
            $continentName = $values[2];
            $areaCode = $values[3];
            $areaName = $values[4];
            $istatCode = $values[5];
            $name = $values[6];
            $atCode = isset($values[9]) && $values[9] !== 'n.d.' ? $values[9] : null;
            $isoAlpha2 = isset($values[11]) && $values[11] !== '' ? $values[11] : null;
            $isoAlpha3 = isset($values[12]) && $values[12] !== '' ? $values[12] : null;

            if (! isset($continents[$continentCode])) {
                $continents[$continentCode] = $this->processContinent($continentName, $continentCode);
            }
            $continentId = $continents[$continentCode];

            // Process area
            $areaKey = "{$continentCode}-{$areaCode}";
            if (! isset($areas[$areaKey])) {
                $areas[$areaKey] = $this->processArea($areaName, $areaCode, $continentId);
            }
            $areaId = $areas[$areaKey];

            $countryId = $this->processCountry(
                $type,
                $name,
                $istatCode,
                $isoAlpha2,
                $isoAlpha3,
                $atCode,
                $continentId,
                $areaId
            );

            $countriesByIstatCode[$istatCode] = $countryId;
        }

        $records = $csv->getRecords();
        foreach ($records as $record) {
            $values = array_values($record);

            if (count($values) < 14) {
                continue;
            }

            $istatCode = $values[5];
            $parentIstatCode = isset($values[13]) && $values[13] !== '' ? $values[13] : null;

            if ($parentIstatCode && isset($countriesByIstatCode[$parentIstatCode])) {
                $this->updateCountryParent($istatCode, $countriesByIstatCode[$parentIstatCode]);
            }
        }

        return count($countriesByIstatCode);
    }

    private function processContinent(string $name, string $istatCode): string
    {
        $model = new ($this->continentModel);

        return $model
            ->setConnection($this->connection ?? config('database.default'))
            ->updateOrCreate(
                ['istat_code' => $istatCode],
                ['name' => $name]
            )->id;
    }

    private function processArea(string $name, string $istatCode, string $continentId): string
    {
        $model = new ($this->areaModel);

        return $model
            ->setConnection($this->connection ?? config('database.default'))
            ->updateOrCreate(
                ['istat_code' => $istatCode],
                [
                    'name' => $name,
                    'continent_id' => $continentId,
                ]
            )->id;
    }

    private function processCountry(
        string $type,
        string $name,
        string $istatCode,
        ?string $isoAlpha2,
        ?string $isoAlpha3,
        ?string $atCode,
        string $continentId,
        string $areaId
    ): string {
        $model = new ($this->countryModel);

        return $model
            ->setConnection($this->connection ?? config('database.default'))
            ->updateOrCreate(
                ['istat_code' => $istatCode],
                [
                    'type' => $type,
                    'name' => $name,
                    'iso_alpha2' => $isoAlpha2,
                    'iso_alpha3' => $isoAlpha3,
                    'at_code' => $atCode,
                    'continent_id' => $continentId,
                    'area_id' => $areaId,
                ]
            )->id;
    }

    private function updateCountryParent(string $istatCode, string $parentId): void
    {
        $model = new ($this->countryModel);

        $model
            ->setConnection($this->connection ?? config('database.default'))
            ->where('istat_code', $istatCode)
            ->update(['parent_country_id' => $parentId]);
    }
}
