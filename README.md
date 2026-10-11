# SilverStripe Shop Comparison Module

Detail specific features of each product, and then compare them in a dedicated interface.

## Features

 * Define product features, eg: weight, wifi
 * Define values for features, eg: 100gm, yes
 * Assign values to products
 * Add / remove products from comparison page view
 * Display a table showing product features side-by-side
 * Group features together for displaying in groups (a feature can belong to several groups)

## Installation

Require the submodule via composer, from site root:

```sh
composer require silvershop/comparison

This module's `main` branch targets Silverstripe 6 and SilverShop `dev-main`.
```

## Running tests

```sh
vendor/bin/phpunit
```

## Static analysis

```sh
composer phpstan
```

### Include in Product template:
``` <% include ProductSpecifications %>```

With grouping enabled:

``` <% include ProductSpecifications Grouping=1 %>```

## Upgrading from single-group features

Features previously belonged to one group (`Feature.GroupID`). They can now
belong to many groups, and their order is stored per group. Running
`dev/build` copies existing group assignments and their sort order onto the
new relation and retires the old `GroupID` and `Sort` columns (renamed to
`_obsolete_*`). It's safe to run more than once.

## License
See LICENCE
