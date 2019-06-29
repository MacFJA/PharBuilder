# Options chain

By default PharBuilder search option value by requesting several options _container_ until one found a result. 

The options are search in this order:

 - [EventOptions](EventOptions.md)
 - [ConsoleOptions](ConsoleOptions.md)
 - [ComposerJson extra section](ComposerJsonOptions.md)
 - [ComposerJson global information](ComposerJsonGenericOptions.md)
 - [ProjectStructureOptions](ProjectStructureOptions.md)
 - [DefaultOptions](DefaultOptions.md)