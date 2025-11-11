# Changelog

All notable changes to `laravel-istat-foreign-countries` will be documented in this file.

## v1.1.0 - 2025-11-11

### ✨ New Features

Automatic unzip mechanism: Added support for handling compressed CSV files (.zip) downloaded from ISTAT

- Package can now automatically download and extract compressed files
- Improved download efficiency by reducing transferred file size
- Automatic handling of temporary files during extraction process

### 🔧 Technical Improvements

- Optimized ISTAT data import process
- Added error handling during decompression phase
- Automatic cleanup of temporary files after import

### 💡 Developer Notes

- The php artisan istat:import command now automatically handles compressed files

A huge thanks to [@Gybra](https://github.com/Gybra) for their first contribution to the project! 🚀 Your work on implementing the unzip mechanism is greatly appreciated and makes this package even better for the community.

## Initial release 1.0.0  - 2025-11-10

Initial release

- Import continents, areas, and foreign countries from ISTAT
- Support for multiple coding standards (ISTAT, ISO alpha2/alpha3, MIN, AT, UNSD)
- Eloquent models with hierarchical relationships
- Support for states and territories with parent relationships
- Artisan command for data import
