# Composer.json extra

To save your build configurations, and don't have to use `build` command or `package` command with all options you can add them in the `composer.json` file of your application.

## Structure

All configurations are saved in the [`extra`](https://getcomposer.org/doc/04-schema.md#extra) section under a _group_ named `phar-builder`.

1. [compression](#compression)
1. [name](#name)
1. [output-dir](#output-dir)
1. [entry-point](#entry-point)
1. [include](#include)
1. [include-dev](#include-dev)
1. [skip-shebang](#skip-shebang)
1. [events](#events)
  1. [build.before](#build.before)
  1. [build.after](#build.after)

### compression

Indicate the compression to use.
Possible values are (case insensitive):
 - No
 - GZip
 - BZip2

The default value is `No`.

### name

The name you want for the PHAR.
The name can't contain a directory separator (` / ` or ` \ `).

The default value is base on the project name in the `composer.json` file.

### output-dir

The directory where the PHAR must be created. The path can be relative, and it will be relative to the `composer.json` file.

The default value is the directory that contain the `composer.json` file.

### entry-point

The path to your application main entrance. The file will be included, it must start your application.

### include

Contain the **LIST** of directories to include.
This is useful when the directory is not a part of your source code (not in any Composer's `autoload` section).

The default value is an empty list.

### include-dev

It's a flag to indicate if _dev only_ packages (like PHPUnit, PHPMD, etc.) and _dev only_ code (like your unit tests) must be included.

Set to `true` (JSON boolean) to add those packages and codes.

The default value is `false`.

### skip-shebang

It's a flag to indicate if the shebang should be skipped or not. This is helpful if you want run a web-project as a phar.

Set to `true` (JSON boolean) to skip the shebang.

The default value is `false`.

### events

During the building process, some events are triggered. This allow you to do some task during the building.

The content of the events name can be a single command or a list of command.


#### command.build.start

This event is triggered in the command `build` after asking all questions to the user and before the actual build.

_Working directory: `composer.json` directory_

#### command.build.after

This event is triggered in the command `build` after the PHAR is created.

_Working directory: `composer.json` directory_

#### command.package.start

This event is triggered in the command `package` just after getting the `composer.json` file to use, but before reading any information for the building.

_Working directory: `composer.json` directory_

#### command.build.after

This event is triggered in the command `package` after the PHAR is created.

_Working directory: `composer.json` directory_

#### unix.interrupt

This event is triggered on **Unix** platform, with a compatible PHP when `Ctrl-C` is press during the PHAR building.

_Working directory: `composer.json` directory_

## Information

If any of those element (except for `compression`, `include`, `include-dev`) are missing you will be prompt to set them.

## Example

Here a full-featured `phar-builder` configuration

```
... The content of your composer.json file
"extra": {
    "phar-builder": {
        "compression": "GZip",
        "name": "application.phar",
        "output-dir": "../",
        "entry-point": "./index.php",
        "include": ["bin","js","css"],
        "include-dev": false,
        "skip-shebang": false,
        "events": {
            "command.package.before" : "git describe --tags > bin/version.txt"
            "command.package.after": [
                "rm bin/version.txt",
                "chmod +x ../application.phar"
            ]
        }
    }
}
```
