<?php

namespace Torann\Modules;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Contracts\Foundation\Application;

class ModuleManager
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Collection|null
     */
    protected $modules = null;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $stubs_path;

    /**
     * Module manager constructor.
     *
     * @param Application $app
     * @param Config      $config
     */
    public function __construct(Application $app, Config $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->stubs_path = $this->app['path.resources'] . '/stubs/modules';

        // Default to the default stubs
        if (file_exists($this->stubs_path) === false) {
            $this->stubs_path = realpath(__DIR__ . '/../resources/stubs');
        }
    }

    /**
     * Runs main seeders for all active modules
     *
     * @param Seeder $seeder
     */
    public function seed(Seeder $seeder)
    {
        $this->withSeeders()->each(function ($module) use ($seeder) {
            /* @var Module $module */
            $seeder->call($module->seederClass());
        });
    }

    /**
     * Load routes for active modules
     *
     * @param Registrar   $router
     * @param string|null $type
     */
    public function loadRoutes(Registrar $router, $type = null)
    {
        $this->withRoutes($type)->each(function ($module) use ($router, $type) {

            /* @var Module $module */
            $router->group(['namespace' => $module->routingControllerNamespace()],
                function ($router) use ($module, $type) {
                    $this->app['files']->getRequire($this->app->basePath() .
                        DIRECTORY_SEPARATOR .
                        $module->routesFilePath($module->routeSuffix(compact('type'))));
                });
        });
    }

    /**
     * Load factories for active modules
     *
     * @param $factory
     */
    public function loadFactories($factory)
    {
        $this->withFactories()->each(function ($module) use ($factory) {
            /* @var Module $module */
            $this->loadFactoryFile($this->app->basePath() . DIRECTORY_SEPARATOR .
                $module->factoryFilePath(), $factory);
        });
    }

    /**
     * Load file
     *
     * @param string $file
     * @param        $factory
     *
     * @codeCoverageIgnore
     */
    protected function loadFactoryFile($file, $factory)
    {
        require $file;
    }

    /**
     * Load service providers for active modules
     */
    public function loadServiceProviders()
    {
        $this->withServiceProviders()->each(function ($module) {
            /* @var Module $module */
            $this->app->register($module->serviceProviderClass());
        });
    }

    /**
     * Get all routable modules (active and having routes file)
     *
     * @param string $type
     *
     * @return Collection
     */
    public function withRoutes($type)
    {
        return $this->filterActiveByMethod('hasRoutes', compact('type'));
    }

    /**
     * Get all routable modules (active and having routes file)
     *
     * @return Collection
     */
    public function withFactories()
    {
        return $this->filterActiveByMethod('hasFactory');
    }

    /**
     * Get all modules that have service providers (active and having service
     * provider file)
     *
     * @return Collection
     */
    public function withServiceProviders()
    {
        return $this->filterActiveByMethod('hasServiceProvider');
    }

    /**
     * Get all modules that have seeders (active and having seeder file)
     *
     * @return Collection
     */
    public function withSeeders()
    {
        return $this->filterActiveByMethod('hasSeeder');
    }

    /**
     * Get active modules that also pass given requirement
     *
     * @param string $requirement
     *
     * @param array  $data
     *
     * @return Collection
     */
    protected function filterActiveByMethod($requirement, array $data = [])
    {
        return $this->modules()
            ->filter(function ($module) use ($requirement, $data) {
                return $module->active() && $module->$requirement($data);
            })->values();
    }

    /**
     * Get all modules
     *
     * @return Collection
     */
    public function all()
    {
        return $this->modules();
    }

    /**
     * Get active modules
     *
     * @return Collection
     */
    public function active()
    {
        return $this->modules()->filter(function ($module) {
            return $module->active();
        })->values();
    }

    /**
     * Load modules (if not loaded) and get modules
     *
     * @return Collection
     */
    protected function modules()
    {
        if ($this->modules === null) {

            $this->modules = new Collection;

            Collection::make($this->config->modules())->each(function ($options, $name) {
                $this->modules->push(new Module($name, $this->app, $options));
            });
        }

        return $this->modules;
    }

    /**
     * Find given module by name
     *
     * @param string $name
     *
     * @return Module
     */
    public function find($name)
    {
        $name = Str::studly($name);

        return $this->modules()->first(function ($module) use ($name) {
            /* @var Module $module */
            return $module->name() == $name;
        });
    }

    /**
     * Verify whether module with given name already exists
     *
     * @param $name
     *
     * @return bool
     */
    public function exists($name)
    {
        return $this->find($name) !== null;
    }

    /**
     * Get the stubs path.
     *
     * @param $path
     *
     * @return string
     */
    public function stubsPath($path)
    {
        return rtrim($this->stubs_path, DIRECTORY_SEPARATOR)
            . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}
