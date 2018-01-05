<?php

namespace Torann\Modules;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Torann\Modules\Console\MakeCommand;
use Torann\Modules\Console\FilesCommand;
use Torann\Modules\Console\MigrationCommand;

class ModulesServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerModuleService();

        if ($this->app->runningInConsole()) {
            $this->registerResources();
            $this->registerModuleCommands();
        }
    }

    /**
     * Register modules services.
     *
     * @return void
     */
    protected function registerModuleService()
    {
        // Register module binding
        $this->app->singleton('modules.config', function ($app) {
            return new Config(
                $app['config']->get('modules'),
                $app['path.config']
            );
        });

        $this->app->singleton('modules', function ($app) {
            return new ModuleManager($app, $app['modules.config']);
        });

        // Register modules providers
        $this->app['modules']->loadServiceProviders();
    }

    /**
     * Register resources.
     *
     * @return void
     */
    public function registerResources()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/modules.php', 'modules'
        );

        if ($this->isLumen() === false) {
            $this->publishes([
                __DIR__ . '/../config/modules.php' => config_path('modules.php'),
            ], 'config');
        }

        // Set stub templates paths
        $this->addStubsTemplatesToPublished();

        // Set migrations paths
        $this->setModulesMigrationPaths();
    }

    /**
     * Add stubs templates to published files
     *
     * @return void
     */
    protected function addStubsTemplatesToPublished()
    {
        // Get base stub path
        $stub_path = realpath(__DIR__ . '/../resources/stubs');

        // Here we get all stubs files from stubs templates directory
        $published_path = $this->app['path.resources'] . DIRECTORY_SEPARATOR . 'stubs/modules';

        // Determine all files to publish
        foreach (glob($stub_path . '/{,.}*.stub', GLOB_BRACE) as $file) {

            // Remove stub path prefix
            $relative_path = preg_replace('/^' . preg_quote($stub_path, '/') . '\//i', '', $file);

            // Add the file for publishing
            $this->publishes([
                $file => $published_path . DIRECTORY_SEPARATOR . $relative_path,
            ], 'stubs');
        }
    }

    /**
     * Register new Artisan commands.
     *
     * @return void
     */
    public function registerModuleCommands()
    {
        $this->commands([
            FilesCommand::class,
            MakeCommand::class,
            MigrationCommand::class,
        ]);
    }

    /**
     * Set migrations paths for all active modules
     */
    protected function setModulesMigrationPaths()
    {
        $paths = new Collection;

        // add to paths all migration directories from modules
        Collection::make($this->app['modules']->active())
            ->each(function ($module) use ($paths) {
                /* @var Module $module */
                $paths->push($module->migrationsPath());
            });

        $this->loadMigrationsFrom($paths->all());
    }

    /**
     * Check if package is running under Lumen app
     *
     * @return bool
     */
    protected function isLumen()
    {
        return str_contains($this->app->version(), 'Lumen') === true;
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [
            'modules',
            'modules.config',
        ];
    }
}