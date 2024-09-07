# Phlex Core

**Collection of PHP Traits for designing object-oriented frameworks.**

[![Unit Testing](https://github.com/x-systems/phlex-core/actions/workflows/unit-tests.yml/badge.svg)](https://github.com/x-systems/phlex-core/actions/workflows/unit-tests.yml)
[![CodeCov](https://codecov.io/gh/x-systems/phlex-core/branch/develop/graph/badge.svg)](https://codecov.io/gh/x-systems/phlex-core)
[![GitHub release](https://img.shields.io/github/release/x-systems/phlex-core.svg)](https://github.com/x-systems/phlex-core/releases)
[![PHPStan enabled](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://phpstan.org)


Phlex Core was created for [Phlex Data](https://github.com/x-systems/phlex-data) and [Phlex UI](https://github.com/x-systems/phlex-ui), but can be used in any other framework too. You will mostly find PHP Traits here, that add functionality into your objects such as:

- Containers: Implements Parent/Child relations between your objects
- Hooks: Create hooks and register callbacks with priorities and arguments
- Initializers: Automatically execute doInitialize() method of your object
- Dynamic Methods: Add methods dynamically into existing objects
- Factory: Specify class name as a string
- App Scope: Inject global "app" object and pass it to new objects

Additionally you a much better 'Exception' class for general-purpose exceptions.

## [Documentation](http://phlex-core.readthedocs.io/en/develop/)

http://phlex-core.readthedocs.io/

##  Install from Composer

```
composer require x-systems/phlex-core
```

## Phlex

Phlex Core is part of [Phlex - PHP UI Framework](https://phlex.dev). If you like this project, you should also look into:

- [Phlex Data](https://github.com/x-systems/phlex-data) - [![GitHub release](https://img.shields.io/github/release/x-systems/phlex-data.svg?label=Phlex+Data)]()
- [Phlex UI](https://github.com/x-systems/phlex-ui) - [![GitHub release](https://img.shields.io/github/release/x-systems/phlex-ui.svg?label=Phlex+UI)]()

