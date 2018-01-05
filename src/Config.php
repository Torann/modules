<?php

namespace Torann\Modules;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Foundation\Application;

class Config
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var Application
     */
    protected $config_path;

    /**
     * Base stub structure.
     *
     * @var array
     */
    protected $stub_mapping = [
        'directories' => [
            'Models',
            'Repositories',
            'Exceptions',
            'Http/Controllers',
            'Database/Migrations',
            'Database/Seeds',
            'Database/Factories',
        ],
        'files' => [
            '{class}ServiceProvider.php' => 'ServiceProvider.php.stub',
            'Models/.gitkeep' => '.gitkeep.stub',
            'Repositories/.gitkeep' => '.gitkeep.stub',
            'Exceptions/.gitkeep' => '.gitkeep.stub',
            'Http/Controllers/.gitkeep' => '.gitkeep.stub',
            'Database/Migrations/.gitkeep' => '.gitkeep.stub',
            'Database/Seeds/.gitkeep' => '.gitkeep.stub',
            'Database/Factories/.gitkeep' => '.gitkeep.stub',
            'Http/Controllers/{class}Controller.php' => 'Controller.php.stub',
            'Models/{class}.php' => 'Model.php.stub',
            'routes/web.php' => 'routes_web.php.stub',
            'routes/api.php' => 'routes_api.php.stub',
            'Database/Seeds/{class}DatabaseSeeder.php' => 'DatabaseSeeder.php.stub',
            'Database/Factories/{class}ModelFactory.php' => 'ModelFactory.php.stub',
            'Repositories/{class}Repository.php' => 'Repository.php.stub',
        ],
        'submodule_files' => [
            'Http/Controllers/{class}Controller.php' => 'Controller.php.stub',
            'Models/{class}.php' => 'Model.php.stub',
            'Database/Seeds/{class}DatabaseSeeder.php' => 'DatabaseSeeder.php.stub',
            'Repositories/{class}Repository.php' => 'Repository.php.stub',
        ],
    ];

    /**
     * Config constructor.
     *
     * @param array $config
     * @param string $config_path
     */
    public function __construct(array $config, $config_path)
    {
        $this->config = $config;
        $this->config_path = $config_path;
    }

    /**
     * Get full path where configuration file should be placed
     *
     * @return string
     */
    public function configPath()
    {
        return $this->config_path
            . DIRECTORY_SEPARATOR
            . "modules.php";
    }

    /**
     * Get value from module configuration.
     *
     * @param string|null $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return $key === null
            ? $this->config
            : Arr::get($this->config, $key, $default);
    }

    /**
     * Get modules configuration.
     *
     * @return array
     */
    public function modules()
    {
        return (array)$this->get('modules', []);
    }

    /**
     * Refresh the packages configuration.
     *
     * @return void
     */
    public function refresh()
    {
        dd($this->configPath());
    }

    /**
     * Get directory where modules will be stored.
     *
     * @return string
     */
    public function directory()
    {
        return $this->get('directory', 'app/Modules');
    }

    /**
     * Get namespace prefix for all modules.
     *
     * @return string
     */
    public function modulesNamespace()
    {
        return $this->get('namespace', 'App\\Modules');
    }

    /**
     * Get the mapping for the given stub section.
     *
     * @param string $key
     *
     * @return array
     * @throws Exception
     */
    public function getStubMap($key)
    {
        if (array_key_exists($key, $this->stub_mapping) === false) {
            throw new Exception("'{$key}' is not a valid stub map.");
        }

        return Arr::get($this->stub_mapping, $key);
    }

    /**
     * Return the file path for the given stub.
     *
     * @param string $stub
     *
     * @return string|false
     */
    public function getFilePath($stub)
    {
        return array_search($stub, $this->stub_mapping['files']);
    }
}
