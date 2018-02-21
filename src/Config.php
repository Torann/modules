<?php

namespace Torann\Modules;

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
     * Config constructor.
     *
     * @param array $config
     * @param string $config_path
     */
    public function __construct(array $config = null, $config_path)
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
        return $this->config_path . DIRECTORY_SEPARATOR . 'modules.php';
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
     * Return the file path for the given stub.
     *
     * @param string $stub
     *
     * @return string|false
     */
    public function getFilePath($stub)
    {
        return Arr::get($this->config, "file_checks.{$stub}", false);
    }
}
