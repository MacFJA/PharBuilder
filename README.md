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
vendor/bin/phar-builder package composer.json
```

### With the phar

```sh
phar-builder.phar package path-to-your-composer.json-file
```

## Options

They are 2 commands available:

* `build` a full interactive phar builder
* `package` a phar builder based on a composer.json

### Command `package`

_Extract from the `vendor/bin/phar-builder help package` command_

```
Usage:
  package [options] [--] [<composer>]

Arguments:
  composer                       The path to the composer.json. If the argument is not provided, it will look for a composer.json file in the current directory

Options:
      --include-dev              Include development packages and path
  -e, --entry-point=ENTRY-POINT  Your application start file
      --compression=COMPRESSION  The compression of your Phar (possible values No, GZip, BZip2)
  -f, --no-compression           Do not compress the Phar
  -z, --gzip                     Set the compression of the Phar to GZip
  -b, --bzip2                    Set the compression of the Phar to BZip2
      --name=NAME                The filename of the Phar archive
  -o, --output-dir=OUTPUT-DIR    The output directory of the Phar archive
  -i, --include=INCLUDE          List of directories to add in Phar (multiple values allowed)
  -h, --help                     Display this help message
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi                     Force ANSI output
      --no-ansi                  Disable ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

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
   │          "include": ["bin","js","css"],
   │          "include-dev": false,
   │          "events": {
   │              "build.before" : "git describe --tags > bin/version.txt",
   │              "build.after": [
   │                  "rm bin/version.txt",
   │                  "chmod +x ../application.phar"
   │              ]
   │          }
   │      }
   │  }
   └
```

[More information about the Composer configuration](docs/ComposerExtra.md)

### Command `build`

The command `build` doesn't take any argument. All options will be ask through the CLI

## Important

 - The only way to add none source code and none vendors files is to use `include` option of the `package` command.
 - The only way to trigger script on build is to use `composer.json` configuration with `package` command.

## Similar projects

 - https://github.com/clue/phar-composer
 - https://github.com/box-project/box2
 - https://github.com/IcecaveStudios/near

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
