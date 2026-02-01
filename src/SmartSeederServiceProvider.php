<?php

namespace Amrachraf6699\SmartSeeder;

use Illuminate\Support\ServiceProvider;
use Amrachraf6699\SmartSeeder\Console\SmartSeedCommand;

class SmartSeederServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     */
    public function register(): void
    {
        $this->commands([
            SmartSeedCommand::class,
        ]);
    }
}
