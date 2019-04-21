# Configuration from Events

This options _container_ use event to get configuration

### The event `options.output`

Allow to indicate where to create the Phar file.

**Event type**: [PathResultEvent](../lib/Options/Event/PathResultEvent.php)

### The event `options.name`

Allow to indicate the name of the Phar

**Event type**: [StringResultEvent](../lib/Options/Event/StringResultEvent.php)

### The event `options.include-dev`

Allow to indicate if development dependencies must be added to the Phar or not

**Event type**: [BooleanResultEvent](../lib/Options/Event/BooleanResultEvent.php)

### The event `options.stub-path`

Allow to set the [stub file](http://php.net/manual/en/phar.fileformat.stub.php) of the Phar

**Event type**: [PathResultEvent](../lib/Options/Event/PathResultEvent.php)

### The event `options.compression`

Allow to indicate the compression to use on the Phar.

The compression value are [PHP Phar constants](http://php.net/manual/en/phar.constants.php#phar.constants.compression).

**Event type**: [IntegerResultEvent](../lib/Options/Event/IntegerResultEvent.php)

### The event `options.entry-point`

Allow to set the script to include (the function [`require_once`](https://www.php.net/manual/en/function.require-once.php) is used) to start your application.

**Event type**: [PathResultEvent](../lib/Options/Event/PathResultEvent.php)

### The event `options.included`

Allow to set additional path to add to the phar.
By default, only path from the composer.json autoloader are included.

Multiple path can be added by separating the values with a coma (`,`).
Path can be a directory or a file.
Path are relative from the **composer.json** directory.

**Event type**: [PathListResultEvent](../lib/Options/Event/PathListResultEvent.php)

### The event `options.excluded`

You can define path to excludes. The path can be partial:

| Value | Match `hello/tests/world` | Match `hellotestsworld` | Match `tests/world` | Match `hello/tests` | Match `testsworld` |
| ----- | ------------------------- | ----------------------- | ------------------- | ------------------- | ------------------- |
| `tests` | **YES** | **YES** | **YES** | **YES** | **YES** |
| `/tests` | _NO_ | _NO_ | **YES** | _NO_ | **YES** |
| `/tests/` | _NO_ | _NO_ | **YES** | _NO_ | _NO_ |
| `*/tests/` | **YES** | _NO_ | **YES** | **YES** | _NO_ |

**Event type**: [PathListResultEvent](../lib/Options/Event/PathListResultEvent.php)