<?php

namespace AmrAchraf\SmartSeeder;

use Illuminate\Support\ServiceProvider;
use AmrAchraf\SmartSeeder\Console\SmartSeedCommand;

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
