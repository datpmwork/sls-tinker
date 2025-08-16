<?php

namespace DatPM\SlsTinker;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use DatPM\SlsTinker\Commands\SlsTinkerCommand;

class SlsTinkerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('sls-tinker')
            ->hasConfigFile()
            ->hasCommand(SlsTinkerCommand::class);
    }
}
