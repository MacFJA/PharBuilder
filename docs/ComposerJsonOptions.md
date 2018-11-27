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

### Set the name of the phar (`name`)

### Set if dev dependencies must be included (`dev`)

### Set if the shebang must be added (`shebang`)

Indicate if a [shebang](https://en.wikipedia.org/wiki/Shebang_(Unix)) must be added on the first line of the phar Stub.

Any shebang in the entry point will be remove to avoid printing the shebang line in the console or on the webpage.

### Set the file to run when the phar is executed (`entry-point`)

### Set the list of files/directories that are not in the autoload and that are needed (`included`)

### Set the list of files/directories to exclude of the phar (`exculded`)

### Set the compression of the phar (`compression`)

The available values are:

 - `bz2`
 - `gzip`
 - `none`
 
Any other value will be ignore, and the next options source will be requested.

