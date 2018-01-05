<?php

namespace Torann\Modules;

use Illuminate\Support\Collection;
use Torann\Modules\Traits\Replacer;
use Torann\Modules\Traits\Normalizer;
use Illuminate\Contracts\Foundation\Application;

class Module
{
    use Normalizer,
        Replacer;

    /**
     * @var
     */
    protected $name;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Torann\Modules\Config
     */
    protected $config;

    /**
     * @var Application
     */
    protected $laravel;

    /**
     * Module constructor.
     *
     * @param string      $name
     * @param Application $application
     * @param array       $options
     */
    public function __construct(
        $name,
        Application $application,
        array $options = []
    )
    {
        $this->name = $name;
        $this->options = Collection::make($options);
        $this->laravel = $application;
        $this->config = $application['modules.config'];
    }

    /**
     * Get module name
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get module seeder class name (with namespace)
     *
     * @param string|null $class
     *
     * @return string
     */
    public function seederClass($class = null)
    {
        return $this->fileClass('seeder', $class);
    }

    /**
     * Get module service provider class
     *
     * @return string
     */
    public function serviceProviderClass()
    {
        return $this->fileClass('serviceProvider');
    }

    /**
     * Get file class
     *
     * @param string      $type
     * @param string|null $class
     *
     * @return string
     */
    protected function fileClass($type, $class = null)
    {
        // Create the base file path
        $path = preg_replace('/^' . preg_quote($this->config->directory(), '/') . '\//i',
            '', $this->{$type . 'FilePath'}());

        // Set class if missing
        $class = $class ?: basename($path, '.php');

        // Build namespace from path
        $namespace = trim(preg_replace('/' . preg_quote(basename($path)) . '$/i', '', $path), '/');
        $namespace = preg_replace('/\//', '\\', $namespace);

        return $this->replace($this->config->modulesNamespace()
            . '\\' . $namespace
            . '\\' . $class,
        $this);
    }

    /**
     * Get module directory
     *
     * @return string
     */
    public function directory()
    {
        return $this->normalizePath($this->config->directory()) .
            DIRECTORY_SEPARATOR . $this->name();
    }

    /**
     * Get module migrations path
     *
     * @param bool $relative
     *
     * @return string
     */
    public function migrationsPath($relative = false)
    {
        $path = 'Database/Migrations';

        return $relative ? $path : $this->directory() . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Verify whether module has service provider
     *
     * @return bool
     */
    public function hasServiceProvider()
    {
        return $this->hasFile('provider', 'serviceProviderFilePath');
    }

    /**
     * Verifies whether module has factory
     *
     * @return bool
     */
    public function hasFactory()
    {
        return $this->hasFile('factory', 'factoryFilePath');
    }

    /**
     * Verifies whether module has routes file
     *
     * @param array $data
     *
     * @return bool
     */
    public function hasRoutes(array $data = [])
    {
        $suffix = $this->routeSuffix($data);

        return $this->hasFile('routes', 'routesFilePath', $suffix);
    }

    /**
     * Verifies whether module has seeder file
     *
     * @return bool
     */
    public function hasSeeder()
    {
        return $this->hasFile('seeder', 'seederFilePath');
    }

    /**
     * Verifies whether module has file of given type either checking config
     * and if it's not exist by checking whether file exists
     *
     * @param string $option
     * @param string $pathFunction
     * @param string $prefix
     *
     * @return bool
     */
    protected function hasFile($option, $pathFunction, $prefix = '')
    {
        return (bool)($this->options->has($prefix . $option)
            ? $this->options->get($prefix . $option)
            : $this->laravel['files']->exists($this->laravel['path.base'] .
                DIRECTORY_SEPARATOR . $this->$pathFunction($prefix)));
    }

    /**
     * Get controller namespace for routing
     *
     * @return string
     */
    public function routingControllerNamespace()
    {
        return $this->config->modulesNamespace() . '\\' . $this->name() . '\\Http\\Controllers';
    }

    /**
     * Get module routes file (with path)
     *
     * @param string $suffix
     *
     * @return string
     */
    public function routesFilePath($suffix)
    {
        return $this->getPath("routes{$suffix}.php.stub");
    }

    /**
     * Get route suffix
     *
     * @param array $data
     *
     * @return string
     */
    public function routeSuffix(array $data)
    {
        return isset($data['type']) ? '_' . $data['type'] : '';
    }

    /**
     * Get module factory file path
     *
     * @return string
     */
    public function factoryFilePath()
    {
        return $this->getPath('ModelFactory.php.stub');
    }

    /**
     * Get module factory file path
     *
     * @return string
     */
    public function seederFilePath()
    {
        return $this->getPath('DatabaseSeeder.php.stub');
    }

    /**
     * Get module service provider file path
     *
     * @return string
     */
    public function serviceProviderFilePath()
    {
        return $this->getPath('ServiceProvider.php.stub');
    }

    /**
     * Get path
     *
     * @param string $file
     *
     * @return string
     */
    protected function getPath($file)
    {
        return $this->directory() . DIRECTORY_SEPARATOR .
            $this->replace($this->config->getFilePath($file), $this);
    }

    /**
     * Verifies whether given module is active
     *
     * @return bool
     */
    public function active()
    {
        return $this->options->get('active', true);
    }
}
