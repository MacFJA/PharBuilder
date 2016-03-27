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
1. [events](#events)
  1. [build.before](#build.before)
  1. [build.after](#build.after)

### compression

Indicate the compression to use.
Possible values are (case insensitive):
 - No
 - None
 - GZip
 - BZip2

The default value is `None`.

### name

The name you want for the PHAR.
The name can't contain a directory separator (` / ` or ` \ `)

### output-dir

The directory where the PHAR must be created. The path can be relative, and it will be relative to the `composer.json` file.

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

### events

During the building process, some events are triggered. This allow you to do some task during the building.

The content of the events name can be a single command or a list of command.


#### build.before

This event is triggered after reading all data for building the PHAR but before the actual build.

_Working directory: `composer.json` directory_

#### build.after

This event is triggered after the PHAR is created.

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
        "events": {
            "build.before" : "git describe --tags > bin/version.txt"
            "build.after": [
                "rm bin/version.txt",
                "chmod +x ../application.phar"
            ]
        }
    }
}
```