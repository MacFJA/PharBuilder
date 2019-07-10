# Configurations in `composer.json` file

You can set configuration for PharBuilder inside you main `composer.json` file.

All options must be added in a `phar-builder` section inside the `extra` section of the `composer.json`.

All options are optional, if not found a value will be guess according to [the configuration chain](OptionsChain.md).

## Full example

```json
{
    "name": "my-vendor/my-awesome-package",
    "extra": {
        "phar-builder": {
            "name": "my-phar",
            "output": "./build/dist/",
            "dev": true,
            "shebang": true,
            "entry-point": "bin/my-app",
            "included": ["HowTo.md", "references/"],
            "excluded": ["src/stubs"],
            "compression": "bz2"
        }
    }
}
```

## Options

Every option are optional.  
If the option is not provided, the next options source will be requested

### Set where to generate the phar (`output`)

Define where the PHAR will be output.

### Set the name of the phar (`name`)

Define the name of the PHAR (without the `.phar` extension)

### Set if dev dependencies must be included (`dev`)

Indicate if development dependencies should be include in the PHAR.  

(Set to `true` to add developments dependencies)

### Set if the shebang must be added (`shebang`)

Indicate if a [shebang](https://en.wikipedia.org/wiki/Shebang_(Unix)) must be added on the first line of the phar Stub.

Any shebang in the entry point will be remove to avoid printing the shebang line in the console or on the webpage.

### Set the file to run when the phar is executed (`entry-point`)

Define the file that will be load by the PHAR when executed.

(Any shebang in this file will be removed)

### Set the list of files/directories that are not in the autoload and that are needed (`included`)

Define the list of directories or files to add along the PHAR.

PharBuilder will automatically add directories and files that are declared in the `autoload` (and `autoload-dev` if requested) section of the `composer.json`, but any additional data your application need must be add by you.

### Set the list of files/directories to exclude of the phar (`excluded`)

You can define path to excludes. The path can be partial:

| Value | Match `hello/tests/world` | Match `hellotestsworld` | Match `tests/world` | Match `hello/tests` | Match `testsworld` |
| ----- | ------------------------- | ----------------------- | ------------------- | ------------------- | ------------------- |
| `tests` | **YES** | **YES** | **YES** | **YES** | **YES** |
| `/tests` | _NO_ | _NO_ | **YES** | _NO_ | **YES** |
| `/tests/` | _NO_ | _NO_ | **YES** | _NO_ | _NO_ |
| `*/tests/` | **YES** | _NO_ | **YES** | **YES** | _NO_ |

### Set the compression of the phar (`compression`)

The available values are:

 - `bz2`
 - `gzip`
 - `none`
 
Any other value will be ignore, and the next options source will be requested.

