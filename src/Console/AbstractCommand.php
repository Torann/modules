<?php

namespace Torann\Modules\Console;

use Exception;
use Torann\Modules\Module;
use Illuminate\Console\Command;
use Torann\Modules\Traits\Replacer;
use Illuminate\Filesystem\Filesystem;
use Torann\Modules\Traits\Normalizer;

abstract class AbstractCommand extends Command
{
    use Normalizer,
        Replacer;

    /**
     * Filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new console command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Run commands
     */
    public function handle()
    {
        // Sanity check
        if (!$this->exists($this->laravel['modules.config']->configPath())) {
            throw new Exception('Config file does not exists. Please run php artisan vendor:publish (see docs for details)');
        }

        $this->proceed();
    }

    /**
     * Run commands
     *
     * @return void
     */
    abstract protected function proceed();

    /**
     * Verify whether given file or directory exists
     *
     * @param string $path
     * @param Module $module
     *
     * @return bool
     */
    protected function exists($path, Module $module = null)
    {
        if ($module !== null) {
            $path = $module->directory() . DIRECTORY_SEPARATOR . $path;
        }

        return $this->files->exists($path);
    }

    /**
     * Creates module directories
     *
     * @param Module $module
     */
    protected function createModuleDirectories(Module $module)
    {
        $directories = $this->laravel['modules.config']->getStubMap('directories');

        foreach ($directories as $directory) {
            $this->createDirectory($module, $directory);
        }
    }

    /**
     * Creates directory
     *
     * @param Module $module
     * @param string $directory
     *
     * @return bool
     * @throws Exception
     */
    protected function createDirectory(Module $module, $directory)
    {
        if (!$this->exists($directory, $module)) {

            $result = $this->files->makeDirectory($module->directory() .
                DIRECTORY_SEPARATOR . $directory, 0755, true);

            if ($result) {
                $this->line("[Module {$module->name()}] Created directory {$directory}");
            }
            else {
                throw new Exception("[Module {$module->name()}] Cannot create directory {$directory}");
            }

            return true;
        }

        return false;
    }

    /**
     * Create files inside given module
     *
     * @param Module $module
     * @param string $sub_module
     *
     * @return bool
     */
    protected function createModuleFiles(Module $module, $sub_module = null)
    {
        // Determine replacements
        $replacements = $sub_module ? ['class' => $sub_module] : [];

        // Get the stub mapping for files
        $files = $this->laravel['modules.config']->getStubMap(
            $sub_module ? 'submodule_files' : 'files'
        );

        foreach ($files as $module_file => $stub_file) {
            $this->copyStubFileIntoModule($module, $stub_file,
                $module_file, $replacements);
        }

        return true;
    }

    /**
     * Copy single stub file into module
     *
     * @param Module $module
     * @param string $stub_file
     * @param string $module_file
     * @param array  $replacements
     *
     * @throws Exception
     */
    protected function copyStubFileIntoModule(
        Module $module,
        $stub_file,
        $module_file,
        array $replacements = []
    )
    {
        $stub_path = $this->laravel['modules']->stubsPath($stub_file);

        if (!$this->exists($stub_path)) {
            throw new Exception("Stub file [{$stub_path}] does NOT exist");
        }

        $module_file = $this->replace($module_file, $module, $replacements);

        if ($this->exists($module_file, $module)) {
            throw new Exception("[Module {$module->name()}] File {$module_file} already exists");
        }

        $this->createMissingDirectory($module, $module_file);
        $this->createFile($module, $stub_path, $module_file, $replacements);
    }

    /**
     * Creates directory for given file (if it doesn't exist)
     *
     * @param Module $module
     * @param string $file
     */
    protected function createMissingDirectory(Module $module, $file)
    {
        if (!$this->exists(($dir = dirname($file)), $module)) {
            $this->createDirectory($module, $dir);
        }
    }

    /**
     * Creates single file
     *
     * @param Module $module
     * @param string $source_file
     * @param string $destination_file
     * @param array  $replacements
     *
     * @throws Exception
     */
    protected function createFile(
        Module $module,
        $source_file,
        $destination_file,
        array $replacements = []
    )
    {
        $result = $this->files->put($module->directory() .
            DIRECTORY_SEPARATOR . $destination_file,
            $this->replace($this->files->get($source_file), $module,
                $replacements)
        );

        if ($result === false) {
            throw new Exception("[Module {$module->name()}] Cannot create file {$destination_file}");
        }

        $this->line("[Module {$module->name()}] Created file {$destination_file}");
    }
}
