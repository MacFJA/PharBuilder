# PharBuilder

Create a Phar (PHp ARchive) file of your Composer based PHP application

## Installation

### Composer

```sh
composer require macfja/phar-builder
```

### Phar archive

You can download the Phar directly on GitHub in the release section (https://github.com/MacFJA/PharBuilder)

## Usage

### Within a composer based project

```sh
vendor/bin/phar-builder
```

### With the phar

```sh
phar-builder.phar package path-to-your-composer.json-file
```

### Command `package`

_Extract from the `vendor/bin/phar-builder help package` command_

```
Description:
  Generate a Phar from a composer.json

Usage:
  package [options] [--] [<composer-json>]

Arguments:
  composer-json                  The path to the composer.json file.
                                 If the argument is not defined, search of a composer.json inside the current directory

Options:
      --output=OUTPUT            Where to output the Phar
      --name=NAME                The name of the Phar
      --no-dev                   Do not include dev dependencies
      --dev                      Include dev dependencies
      --no-shebang               Do not add/remove shebang
      --shebang                  Ensure that a shebang is used
      --entry-point=ENTRY-POINT  The file to include when the Phar is executed/called
      --included=INCLUDED        The list (separate by ",") of path to add in the Phar
      --excluded=EXCLUDED        The list (separate by ",") of path to exclude in the Phar
      --bz2                      Use the BZip2 compression for the Phar
      --gzip                     Use the GZip compression for the Phar
      --flat                     Do not compress the Phar
  -h, --help                     Display this help message
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi                     Force ANSI output
      --no-ansi                  Disable ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

```

[More information about the Composer configuration](docs/ComposerJsonOptions.md)

## Similar projects

 - https://github.com/clue/phar-composer
 - https://github.com/box-project/box2
 - https://github.com/IcecaveStudios/near
 - https://github.com/Modularr/Phar
 - https://github.com/kos33rd/pharaon
 - https://github.com/brad-jones/pharbuilder
 - https://github.com/index0h/yii2-phar
 - https://github.com/keradus/PharBuilder
 - https://github.com/JeroenDeDauw/PharBuilder
 - https://github.com/theseer/Autoload
 - https://github.com/crodas/Phar-Builder
 - https://github.com/oleics/php-ac-build-phar
 - https://github.com/mvccore/packager
 - https://github.com/box-project/box2-lib
 - https://github.com/humbug/box

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.