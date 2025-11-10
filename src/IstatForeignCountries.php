<?php

namespace PlinCode\IstatForeignCountries;

use PlinCode\IstatForeignCountries\Services\ForeignCountriesImportService;

class IstatForeignCountries
{
    public function __construct(
        private ForeignCountriesImportService $importService
    ) {}

    public function import(): int
    {
        return $this->importService->execute();
    }
}
