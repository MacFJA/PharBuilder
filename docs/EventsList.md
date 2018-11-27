# List of events of PharBuilder

**PharBuilder** use [Event](http://event.thephpleague.com/2.0/) library from [The League of Extraordinary Packages](https://thephpleague.com/).

You can [interact with any event dispatched by **PharBuilder**](UseEvent.md).

## List of events and when they are triggered

Event name            | When                                                | Why | What
----------------------|-----------------------------------------------------|-----|-----
`execution.before`    | Just before doing anything                          | To add file before creating the PHAR, to compile files
`builder.after`       | When all files have been added to the PHAR          | To add more files | The [Builder](../lib/Builder/ArchiveBuilder.php)
`compressor.before`   | Just before starting the compression                | To change the compression | The [Compressor](../lib/Builder/ArchiveCompressor.php)
`execution.after`     | After everything if created and compressed          | To cleanup | The [stats](../lib/Builder/Stats.php) about the PHAR creation
`builder.add`         | A directory/file will be added to the PHAR          | | A [BuilderAddEvent](../lib/Builder/Event/BuilderAddEvent.php) that contains the path and some metadata
`options.output`      | When the output directory is requested              | To provide a custom output path | An optional result
`options.name`        | When the name of the PHAR is requested              | To provide a custom name | An optional result
`options.include-dev` | When the inclusion of dev dependencies is requested | To change if the dev dependencies must be included or not | An optional result
`options.stub-path`   | When the path of the PHAR stub is requested         | To provide a custom path for the PHAR stub | An optional result
`options.compression` | When the compression is requested                   | To provide a custom compression | An optional result
`options.entry-point` | When the entry point is requested                   | To provide a custom entry point | An optional result
`options.included`    | When the list of path to add is requested           | To provide a custom list of path | An optional result
`options.excluded`    | When the list of path to don't add is requested     | To provide a custom list of path | An optional result