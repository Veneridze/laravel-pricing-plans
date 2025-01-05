<?php

namespace Veneridze\PricingPlans;


use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;

class PricingPlansServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-pricing-plans')
            ->hasMigrations([
                'create_plans_tables',
            ])
            ->hasAssets()
            ->hasTranslations()
            ->hasConfigFile()
            ->publishesServiceProvider('PricingPlansServiceProvider')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishMigrations()
                    ->publishConfigFile()
                    ->publishAssets()
                    ->copyAndRegisterServiceProviderInApp();
            });
    }

    public function packageBooted(): void {}

    public function packageRegistered(): void {}
}
