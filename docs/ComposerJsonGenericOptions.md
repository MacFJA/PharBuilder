# Generic configurations from `composer.json` file

PharBuilder will read several information from a standard `composer.json` file.

## Options

PharBuilder will read the `composer.json`:
 - directory path to set the PHAR output directory (same directory as the `composer.json`)
 - **`name`** to guess the PHAR name (remove the vendor name)
 - **`bin`** to guess the entry-point (will take the first path)