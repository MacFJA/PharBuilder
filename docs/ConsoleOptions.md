# Configuration from the CLI

You can define configuration option by passing them in the command

## The `--output` configuration

You can define the path to the directory where the final PHAR will be put.

**Usage**: `phar-builder package --output=/tmp/ composer.json`

## The `--name` configuration

You can define the name of the phar (extension excluded)

**Usage**: `phar-builder package --name=my-phar composer.json`

## The `--dev`/`--no-dev` configuration

Indicate if development dependencies should be included in the final phar.

- **Usage**: `phar-builder package --dev composer.json`
- **Usage**: `phar-builder package --no-dev composer.json`

## The `--shebang`/`--no-shebang` configuration

Indicate if the [_shebang_](https://en.wikipedia.org/wiki/Shebang_(Unix\)) (`#!/usr/bin/env php`) should be added in the phar start script.

(_Shebang_ are always remove from the your entry-point to avoid double _shebang_)

- **Usage**: `phar-builder package --shebang composer.json`
- **Usage**: `phar-builder package --no-shebang composer.json`

## The `--entry-point` configuration

You can define the PHP script to include (the function [`require_once`](https://www.php.net/manual/en/function.require-once.php) is used) to start your application.

**Usage**: `phar-builder package --entry-point=bin/application.php composer.json`

## The `--included` configuration

You can define additional path to add to the phar.
By default, only path from the composer.json autoloader are included.

Multiple path can be added by separating the values with a coma (`,`).
Path can be a directory or a file.
Path are relative from the **composer.json** directory.

**Usage**: `phar-builder package --included=images,README.md composer.json`

## The `--excluded` configuration

You can define path to excludes. The path can be partial:

| Value | Match `hello/tests/world` | Match `hellotestsworld` | Match `tests/world` | Match `hello/tests` | Match `testsworld` |
| ----- | ------------------------- | ----------------------- | ------------------- | ------------------- | ------------------- |
| `tests` | **YES** | **YES** | **YES** | **YES** | **YES** |
| `/tests` | _NO_ | _NO_ | **YES** | _NO_ | **YES** |
| `/tests/` | _NO_ | _NO_ | **YES** | _NO_ | _NO_ |
| `*/tests/` | **YES** | _NO_ | **YES** | **YES** | _NO_ |

**Usage**: `phar-builder package --excluded=tests,fixture composer.json`

## The `--bz2`/`--gzip`/`--flat` configuration

You can define the compression of the phar with `--bz2` for BZip2, `--gzip` for GZip or `--flat` for no compression.

The compression is dependant of your platform when creating the phar, but also on the destination platform for executing.

# The Console options list

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

_Run the command `phar-builder package --help` to see this help_