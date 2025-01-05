<?php

namespace Veneridze\PricingPlans\Tests;

use Faker\Generator as FakerGenerator;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;
//use Illuminate\Database\Eloquent\Factory
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Veneridze\PricingPlans\PricingPlansServiceProvider;
use Veneridze\PricingPlans\Tests\Models\User;
use Illuminate\Foundation\Testing\TestCase as Testbench;

class TestCase extends Testbench
{
    public function createApplication() {}

    /**
     * Setup the test environment.
     *
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        // Run Laravel migrations
        /*$this->loadLaravelMigrations('testbench');

        // Run package migrations
        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--realpath' => true,
            '--path' => realpath(__DIR__ . '/../resources/migrations')
        ]);*/
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->registerEloquentFactory($app);
        // Set user model
        $app['config']->set('auth.providers.users.model', User::class);
        // set up database configuration
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Get Laraplans package service provider.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    public function getPackageProviders($app)
    {
        return [
            ConsoleSupportServiceProvider::class,
            PricingPlansServiceProvider::class,
        ];
    }

    /**
     * Register the Eloquent factory instance in the container.
     *
     * @return void
     */
    protected function registerEloquentFactory($app)
    {
        $app->singleton(FakerGenerator::class, function () {
            return FakerFactory::create();
        });
        $app->singleton(EloquentFactory::class, function ($app) {
            $faker = $app->make(FakerGenerator::class);
            return EloquentFactory::construct($faker, __DIR__ . '/../resources/factories');
        });
    }
}
