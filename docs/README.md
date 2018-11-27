# Welcome on PharBuilder Documentation

The goal of PharBuilder is to create an executable PHAR (PHp ARchive) with the minimal configuration possible.  
(No configuration at all is possible most of cases)

To achieve this, it rely on the `composer.json` file of the project and some guessing.

## How it's works

When the command `package` (the default command) is launch, it will try to find its needed data be looking (in that order):

 1. [Event based result](EventOptions.md)
 2. [Option from the CLI](ConsoleOptions.md)
 3. [ComposerJson extra section](ComposerJsonOptions.md)
 4. [ComposerJson global information](ComposerJsonGenericOptions.md)
 5. [Guess from the project structure](ProjectStructureOptions.md)
 6. [Fallback on predefined values](DefaultOptions.md)

With this data, it will parse your `composer.lock` to find your dependency and create a PHAR according to its data.

## Expansibility

You can change how **PharBuilder** behave with the help of some events.

**PharBuilder** dispatch some events that can be use to add custom logic in the PHAR creation process.

## Limitations

Whereas it provide some configurations, **PharBuilder** is not very flexible, it's not its goal.

You can't define a fine list of file to excluded or include.

You can't prevent dependency collision like [box](https://github.com/humbug/box) do.