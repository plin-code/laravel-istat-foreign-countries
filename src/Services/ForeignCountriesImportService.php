<?php

namespace PlinCode\IstatForeignCountries\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Bom;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\UnavailableFeature;
use League\Csv\UnavailableStream;
use RuntimeException;
use ZipArchive;

class ForeignCountriesImportService
{
    private string $csvUrl;

    private string $tempFilename;

    private string $tempZipFilename;

    private ?string $connection;

    private string $continentModel;

    private string $areaModel;

    private string $countryModel;

    public function __construct()
    {
        $this->csvUrl = config('istat-foreign-countries.import.csv_url');
        $this->tempFilename = config('istat-foreign-countries.import.temp_filename');
        $this->tempZipFilename = config('istat-foreign-countries.import.temp_zip_filename');
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
        $contentType = $response->header('Content-Type');

        $isZipped = $contentType === 'application/zip' || $contentType === 'application/x-zip-compressed';

        if ($response->failed()) {
            throw new \RuntimeException('Failed to download CSV from ISTAT');
        }

        $fileName = $isZipped ? $this->tempZipFilename : $this->tempFilename;

        $storage->put($fileName, $response->body());

        $downloadedFile = $storage->path($fileName);

        if ($isZipped) {
            $downloadedFile = $this->unzip($storage, $downloadedFile);
        }

        return $downloadedFile;
    }

    private function unzip(Filesystem $storage, string $downloadedFile): string
    {
        $zip = new ZipArchive;
        if ($zip->open($downloadedFile) === true) {
            $extractedFolder = 'istat_foreign_countries_extracted';
            $extractPath = $storage->path($extractedFolder);
            if ($storage->exists($extractedFolder)) {
                $storage->deleteDirectory($extractedFolder);
            }
            $storage->makeDirectory($extractedFolder);

            $csvFile = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (Str::endsWith(strtolower($filename), '.csv')) {
                    $zip->extractTo($extractPath, $filename);

                    $oldPath = $extractPath.DIRECTORY_SEPARATOR.$filename;
                    $newPath = $storage->path($this->tempFilename);
                    rename($oldPath, $newPath);

                    $csvFile = $storage->path($this->tempFilename);
                }
            }

            $zip->close();

            $storage->delete($this->tempZipFilename);
            $storage->deleteDirectory($extractedFolder);

            if (! blank($csvFile)) {
                return $csvFile;
            } else {
                throw new RuntimeException('No CSV found inside the ZIP');
            }
        } else {
            throw new RuntimeException('Failed to open ZIP file '.$downloadedFile);
        }
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

        $continents = [];
        $areas = [];
        $countriesByIstatCode = [];

        // First pass: create continents, areas and countries
        foreach ($records as $record) {
            // Skip if essential columns are missing
            if (! isset(
                $record['Stato(S)/Territorio(T)'],
                $record['Codice Continente'],
                $record['Denominazione Continente (IT)'],
                $record['Codice Area'],
                $record['Denominazione Area (IT)'],
                $record['Codice ISTAT'],
                $record['Denominazione IT']
            )) {
                continue;
            }

            $type = $record['Stato(S)/Territorio(T)'];
            $continentCode = $record['Codice Continente'];
            $continentName = $record['Denominazione Continente (IT)'];
            $areaCode = $record['Codice Area'];
            $areaName = $record['Denominazione Area (IT)'];
            $istatCode = $record['Codice ISTAT'];
            $name = $record['Denominazione IT'];
            $atCode = isset($record['Codice AT']) && $record['Codice AT'] !== 'n.d.' ? $record['Codice AT'] : null;
            $isoAlpha2 = isset($record['Codice ISO 3166 alpha2']) && $record['Codice ISO 3166 alpha2'] !== '' ? $record['Codice ISO 3166 alpha2'] : null;
            $isoAlpha3 = isset($record['Codice ISO 3166 alpha3']) && $record['Codice ISO 3166 alpha3'] !== '' ? $record['Codice ISO 3166 alpha3'] : null;

            if (blank($name)) {
                continue;
            }

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
            if (! isset($record['Codice ISTAT'])) {
                continue;
            }

            $istatCode = $record['Codice ISTAT'];
            $parentIstatCode = isset($record['Codice ISTAT_Stato Padre']) && $record['Codice ISTAT_Stato Padre'] !== '' ? $record['Codice ISTAT_Stato Padre'] : null;

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
