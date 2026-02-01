# Laravel Smart Seeder

Enhanced interactive seeding for Laravel projects. This package wraps the default `db:seed` experience in a friendly menu, lets you selectively run seeders, and supports shortcuts for automation.

## Features

- Interactive menu to run either every seeder, the `DatabaseSeeder`, or hand-pick one or more classes.
- Automatic discovery of classes inside `database/seeders`, including nested folders.
- Support for `--force` to skip prompts and `--class=` to target a single seeder directly.
- Graceful feedback when seeders are missing or a choice could not be resolved.
- Compatible with Laravel 9, 10, and 11 via PSR-4 autoloading and package-auto-discovery.

## Installation

```bash
composer require amr-achraf/laravel-smart-seeder
```

Laravel detects the package automatically thanks to its auto-discovery entry in `composer.json`.

## Usage

### Run interactive menu

```bash
php artisan db:smart-seed
```

Sample interaction:

```
What would you like to do?
  [0] Run every seeder inside database/seeders
  [1] Run the DatabaseSeeder entry point
  [2] Choose one or more seeders manually
 > 2

Seeders available in database/seeders:
  [1] UsersTableSeeder
  [2] ProductsTableSeeder
  [3] OrdersTableSeeder

Enter the number(s) or class name(s) you want to run (comma separated) > 1,3
 → Database\Seeders\UsersTableSeeder
 → Database\Seeders\OrdersTableSeeder
 ```

### Force running all seeders

```bash
php artisan db:smart-seed --force
```

### Run a specific seeder

```bash
php artisan db:smart-seed --class=UsersTableSeeder
```

## Options

- `--force`: skips the menu and runs every discovered seeder sequentially.
- `--class=`: accepts a fully qualified class name or a short seeder name; it bypasses prompts and runs only that class.

## Supported Laravel versions

Laravel 9.x, 10.x, and 11.x.

## Contributing

1. Fork the repository and create a feature branch.
2. Follow PSR-12 formatting and keep the command logic isolated inside `src/Console`.
3. Open a pull request describing the change.

Please run `composer test` (currently a placeholder) before proposing changes.

## License

MIT © Amr Achraf
