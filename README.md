# Laravel Smart Seeder [![Packagist Version](https://img.shields.io/packagist/v/amrachraf6699/laravel-smart-seeder.svg)](https://packagist.org/packages/amrachraf6699/laravel-smart-seeder)

Smart, conversational control over your database seeders. Instead of memorizing class names or blindly re-running `php artisan db:seed`, Smart Seeder walks you through a friendly menu where you can explore every available seeder, choose one (or more), or run everything without typing extra arguments.

Use it whenever you want a guided seeding experience—whether you're onboarding a teammate, debugging a migration, or scripting up something for CI where clarity matters.

## Highlights

- A warm prompt that asks whether to run every seeder, the `DatabaseSeeder` entry point, or hand-pick one or more classes.
- Intelligent discovery of classes inside `database/seeders`, including nested folders, without any extra configuration.
- Multiple-selection mode accepts indexes, full class names, or even fuzzy matches so you can type `users` instead of the entire namespace.
- `--force` and `--class=` shortcuts for automation-friendly workflows (CI, scripts, or when you know exactly what you want).
- Gentle error messages when a seeder is missing or your selection is not understood.

## Installation

```bash
composer require amrachraf6699/laravel-smart-seeder
```

Laravel auto-discovers the service provider, so the command is ready right away.

## Usage (friendly mode)

```
$ php artisan db:smart-seed
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

The prompt accepts indexes, class names, or partial names, so you can type `products` instead of the full namespace and still hit the right target.

## Quick shortcuts

Use `--force` when you want to run every discovered seeder without any prompts:

```bash
php artisan db:smart-seed --force
```

Target a specific seeder directly in scripts or automation with `--class=`:

```bash
php artisan db:smart-seed --class=UsersTableSeeder
```

## Options & Flags

- `--force`: skip prompts and seed everything that was found under `database/seeders`.
- `--class=`: execute a fully qualified or short seeder name immediately.

## Supported Laravel versions

Laravel 8.x through 12.x (the package sticks to standard service providers and works with PHP 8.1+).

## Contributing & feedback

1. Fork the repo and create a feature branch with descriptive commits.
2. Keep the command logic inside `src/Console` and follow PSR-12 formatting.
3. Open a pull request and share how you’re using the command or what feedback you have.

Run `composer test` (placeholder stub) before submitting, or explain why tests aren’t available yet.

## License

MIT © Amr Achraf
