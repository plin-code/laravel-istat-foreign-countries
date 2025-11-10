<?php

namespace PlinCode\IstatForeignCountries\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PlinCode\IstatForeignCountries\Services\ForeignCountriesImportService;

class IstatForeignCountriesCommand extends Command
{
    public $signature = 'foreign-countries:import';

    public $description = 'Import continents, areas and foreign countries from ISTAT';

    public function __construct(
        private readonly ForeignCountriesImportService $importService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting foreign countries data import...');

        try {
            $count = DB::transaction(fn () => $this->importService->execute());

            $this->info("Import completed successfully! Imported {$count} countries.");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error during import: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
