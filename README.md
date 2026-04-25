# SilverStripe Shop Comparison Module

Detail specific features of each product, and then compare them in a dedicated interface.

## Features

 * Define product features, eg: weight, wifi
 * Define values for features, eg: 100gm, yes
 * Assign values to products
 * Add / remove products from comparison page view
 * Display a table showing product features side-by-side
 * Group features together for displaying in groups

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

## License
See LICENCE
