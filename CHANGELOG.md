# Changelog

All notable changes to `laravel-istat-foreign-countries` will be documented in this file.

## v1.1.4 - 2026-05-19

### Fix

- Sanitize optional ISTAT fields (`iso_alpha2`, `iso_alpha3`, `at_code`, `parent_country_id`) by converting empty strings and common placeholders (`n.d.`, `n/a`, `-`, `null`, case-insensitive, trimmed) to `null` before insert. Previously these values leaked into the database and broke length-bound columns like ISO alpha2/alpha3.
- Extract sanitization into a private `sanitizeIstatField()` helper so all optional fields share one consistent rule.

Thanks to [@dpapperini](https://github.com/dpapperini) for the contribution in [#12](https://github.com/plin-code/laravel-istat-foreign-countries/pull/12).

## v1.1.3 - 2026-04-02

### Fix

- Remove `hasViews()` from service provider. The package has no views directory, causing `DirectoryNotFoundException` on production deployments.

## v1.1.2 - 2026-04-02

### Fix

- Fix `countries` table migration failing on PostgreSQL when creating self-referencing foreign key `parent_country_id`
- Split the FK constraint into a separate `Schema::table` call that runs after table creation
- SQLite and MySQL are unaffected

## v1.1.1 - 2025-11-26

### What's Changed

* Bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/plin-code/laravel-istat-foreign-countries/pull/4
* Fix: empty blank line bypass by @Gybra in https://github.com/plin-code/laravel-istat-foreign-countries/pull/5

**Full Changelog**: https://github.com/plin-code/laravel-istat-foreign-countries/compare/1.1.0...1.1.1

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
