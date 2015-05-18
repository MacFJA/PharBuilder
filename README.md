# PharBuilder

Create a Phar (PHp ARchive) file of your Composer based PHP application

## Installation

### Composer

```sh
composer require macfja/phar-builder @dev
```

### Phar archive

You can download the Phar directly on GitHub (https://github.com/MacFJA/PharBuilder)

## Usage

### Within a composer based project

```sh
vendor/bin/phar-builder package composer.json
```

### With the phar

```sh
php phar-builder.phar package path-to-your-composer.json-file
```

## Options

They are 2 commands available:

* `build` a full interactive phar builder
* `package` a phar builder based on a composer.json

### Command `package`

_Extract from the `vendor/phar-builder help package` command_

```
Usage:
 package [-e|--entry-point="..."] [--compression="..."] [-f|--no-compression] [-z|--gzip] [-b|--bzip2] [--name="..."] [-o|--output-dir="..."] [-i|--include="..."] composer

Arguments:
 composer              The path to the composer.json

Options:
 --entry-point (-e)    Your application start file
 --compression         The compression of your Phar (possible values No, GZip, BZip2)
 --no-compression (-f) Do not compress the Phar
 --gzip (-z)           Set the compression of the Phar to GZip
 --bzip2 (-b)          Set the compression of the Phar to BZip2
 --name                The filename of the Phar archive
 --output-dir (-o)     The output directory of the Phar archive
 --include (-i)        List of directories to add in Phar (multiple values allowed)
 --help (-h)           Display this help message
 --quiet (-q)          Do not output any message
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version
 --ansi                Force ANSI output
 --no-ansi             Disable ANSI output
 --no-interaction (-n) Do not ask any interactive question

Help:
 Create a Phar file of a composer project.
 The command can get values from CLI argument, by reading composer file or by ask (in this order)
 If an option is both defined in the composer file and in the CLI argument, the CLI argument will be used.
 Example of a composer configuration:

   ┌
   │  ... The content of your composer.json file
   │  "extra": {
   │      "phar-builder": {
   │          "compression": "GZip",
   │          "name": "application.phar",
   │          "output-dir": "../",
   │          "entry-point": "./index.php",
   │          "include": ["bin","js","css"]
   │      }
   │  }
   └
```

### Command `build`

The command `build` doesn't take any argument. All options will be ask through the CLI

## Important

The only way to add none source code and none vendors files is to use `include` option of the `package` command

## Similar projects

 - https://github.com/clue/phar-composer
 - https://github.com/box-project/box2