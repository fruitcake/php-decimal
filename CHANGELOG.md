# Changelog

## 2.0.0 - Unreleased

### Changed
- **BREAKING**: Minimum PHP version is now 8.4 (was 8.1)
- **BREAKING**: Internal storage now uses `BcMath\Number` instead of unit values
- Added `ext-bcmath` as a required extension
- Upgraded PHPUnit to version 11
- Internal calculations use scale 20 for higher precision

### Added
- `getValue()` method to access the internal `BcMath\Number` instance
- `toString()` now accepts an optional precision parameter for custom output precision

### Fixed
- Improved precision for complex calculations (divide then multiply no longer loses precision)

## 1.0.0 - 2024-01-01

### Added
- Initial release
- `Decimal` class with precision-based decimal arithmetic
- Locale parsing support
- Comparison methods
- Arithmetic operations (add, sub, multiply, divide)
