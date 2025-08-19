<?php

namespace DatPM\SlsTinker;

use DatPM\SlsTinker\Commands\SlsTinkerCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
