<?php

namespace Amrachraf6699\SmartSeeder\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class SmartSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:smart-seed
                            {--force : Run every seeder without prompts}
                            {--class= : Specify a seeder class to run (non-interactive)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactive seeder runner that lets you pick which seeders should execute.';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle(): int
    {
        if ($classOption = $this->option('class')) {
            return $this->handleClassOption($classOption);
        }

        $seeders = $this->discoverSeeders();

        if ($this->option('force')) {
            return $this->runAllSeeders($seeders, true, true);
        }

        if (empty($seeders)) {
            $this->warn('No seeder classes were detected inside database/seeders.');
            $this->info('Create seeders first (php artisan make:seeder) and run this command again.');

            return 1;
        }

        $action = $this->promptAction();

        return match ($action) {
            'all' => $this->runAllSeeders($seeders, false, true),
            'database' => $this->runDatabaseSeeder(),
            'picker' => $this->runSelectedSeeders($seeders),
            default => 1,
        };
    }

    protected function promptAction(): string
    {
        $actions = [
            'all' => 'Run every seeder inside database/seeders',
            'database' => 'Run the DatabaseSeeder entry point',
            'picker' => 'Choose one or more seeders manually',
        ];

        $selection = $this->choice('What would you like to do?', array_values($actions));

        return array_search($selection, $actions, true) ?: 'all';
    }

    protected function discoverSeeders(): array
    {
        $seedPath = database_path('seeders');

        if (! $this->files->isDirectory($seedPath)) {
            return [];
        }

        $classes = [];

        foreach ($this->files->allFiles($seedPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace('/', '\\', $file->getRelativePathname());
            $class = 'Database\\Seeders\\' . Str::beforeLast($relative, '.php');
            $class = trim($class, '\\');

            if (! class_exists($class)) {
                continue;
            }

            $classes[] = $class;
        }

        return $this->normalizeSeeders(array_values($classes));
    }

    protected function normalizeSeeders(array $seeders): array
    {
        $unique = array_unique($seeders);

        sort($unique, SORT_STRING);

        return array_values($unique);
    }

    protected function runAllSeeders(array $seeders, bool $isForce = false, bool $skipDatabaseSeeder = false): int
    {
        if ($skipDatabaseSeeder) {
            $seeders = array_values(array_filter($seeders, static function (string $class): bool {
                return $class !== 'Database\\Seeders\\DatabaseSeeder';
            }));

            if (empty($seeders)) {
                $this->warn('Only DatabaseSeeder is registered; nothing left to run in "Run every seeder" mode.');

                return 1;
            }
        }

        if (empty($seeders)) {
            $this->warn('No seeders found to run.');

            return 1;
        }

        $this->info('Running every seeder in database/seeders.');

        $status = 0;

        foreach ($seeders as $class) {
            $status = $this->runSeeder($class, $isForce);

            if ($status !== 0) {
                $this->error("Seeder {$class} returned non-zero status ({$status}).");

                break;
            }
        }

        return $status;
    }

    protected function runDatabaseSeeder(): int
    {
        $class = 'Database\\Seeders\\DatabaseSeeder';

        if (! class_exists($class)) {
            $this->warn('DatabaseSeeder was not found.');
            $this->info('Consider creating DatabaseSeeder or run a specific seeder.');

            return 1;
        }

        $this->info('Running DatabaseSeeder...');

        return $this->runSeeder($class, true);
    }

    protected function runSelectedSeeders(array $seeders): int
    {
        $selection = $this->promptForSeeders($seeders);

        if (empty($selection)) {
            $this->error('No seeders were selected.');

            return 1;
        }

        return $this->runMultipleSeeders($selection);
    }

    protected function runMultipleSeeders(array $seeders): int
    {
        $status = 0;

        foreach ($seeders as $class) {
            $status = $this->runSeeder($class, true);

            if ($status !== 0) {
                $this->error("Seeder {$class} failed.");
                break;
            }
        }

        return $status;
    }

    protected function runSeeder(string $class, bool $force = false): int
    {
        if (! class_exists($class)) {
            $this->error("Seeder class {$class} does not exist.");

            return 1;
        }

        $this->line(" → {$class}");

        $arguments = ['--class' => $class];

        if ($force) {
            $arguments['--force'] = true;
        }

        return $this->call('db:seed', $arguments);
    }

    protected function promptForSeeders(array $seeders): array
    {
        $map = $this->seedersMap($seeders);

        $this->info('Seeders available in database/seeders:');

        foreach ($map as $index => $class) {
            $this->line("  [{$index}] " . $this->shortClassName($class));
        }

        $input = $this->ask('Enter the number(s) or class name(s) you want to run (comma separated)');

        $selection = $this->resolveSelection($input, $map);

        if (empty($selection)) {
            $this->error('The selection was invalid.');

            return $this->promptForSeeders($seeders);
        }

        return $selection;
    }

    protected function seedersMap(array $seeders): array
    {
        $map = [];
        $index = 1;

        foreach ($seeders as $class) {
            $map[$index++] = $class;
        }

        return $map;
    }

    protected function resolveSelection(string $input, array $map): array
    {
        $tokens = array_filter(preg_split('/[,\s]+/', $input, -1, PREG_SPLIT_NO_EMPTY));

        $matched = [];

        foreach ($tokens as $token) {
            if (is_numeric($token)) {
                $index = (int) $token;

                if (isset($map[$index])) {
                    $matched[] = $map[$index];

                    continue;
                }
            }

            $normalized = trim($token, '\\');

            foreach ($map as $class) {
                $short = $this->shortClassName($class);

                if (strcasecmp($short, $normalized) === 0 || strcasecmp($class, $normalized) === 0) {
                    $matched[] = $class;

                    continue 2;
                }

                if (Str::contains(Str::lower($class), Str::lower($normalized))) {
                    $matched[] = $class;

                    continue 2;
                }
            }
        }

        return array_values(array_unique($matched));
    }

    protected function shortClassName(string $class): string
    {
        return Str::afterLast($class, '\\');
    }

    protected function handleClassOption(string $class): int
    {
        $qualified = $this->qualifyClass($class);

        if (! class_exists($qualified)) {
            $this->error("Seeder class {$qualified} does not exist.");

            return 1;
        }

        $this->info("Running {$qualified} via --class option.");

        return $this->runSeeder($qualified, true);
    }

    protected function qualifyClass(string $class): string
    {
        $class = trim($class, '\\');

        if (class_exists($class)) {
            return $class;
        }

        if (Str::startsWith($class, 'Database\\Seeders\\')) {
            return $class;
        }

        return 'Database\\Seeders\\' . str_replace('/', '\\', $class);
    }
}
