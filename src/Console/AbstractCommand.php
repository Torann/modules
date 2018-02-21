<?php

namespace Torann\Modules\Console;

use Exception;
use SplFileInfo;
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

        foreach ($this->getModuleFiles($sub_module) as $source=>$destination) {
            $this->copyStubFileIntoModule(
                $module,
                $source,
                $destination,
                $replacements
            );
        }

        return true;
    }

    /**
     * Create files inside given module
     *
     * @param string $sub_module
     * @param array  $files
     *
     * @return array
     */
    protected function getModuleFiles($sub_module = null, array $files = [])
    {
        // If this is not a submodule then just return an
        // array of all of the module stub files.
        if ($sub_module === null) {
            $values = $this->files->allFiles(
                $this->laravel['modules']->stubsPath('module'),
                true
            );
        }

        // Map all of the submodule files to absolute paths
        else {
            $values = array_map(function($file) {

                // Check for an override file in the submodule stub directory.
                if (file_exists(
                    $override = $this->laravel['modules']->stubsPath("submodule/{$file}")
                )) {
                    return $override;
                }

                return $this->laravel['modules']->stubsPath("module/{$file}");
            }, $this->laravel['modules.config']->get('submodule'));
        }

        // Create a regex friendly version of the stubs path
        $path = preg_quote($this->laravel['modules']->stubsPath(), DIRECTORY_SEPARATOR);

        foreach ($values as $file) {

            // Just get the file path
            $file = ($file instanceof SplFileInfo) ? $file->getPathname() : $file;

            // Ignore all non-stub extension files
            if (substr(basename($file), -5) !== '.stub') continue;

            // Map the file as source and destination
            $files[$file] = preg_replace("/^{$path}\/(module|submodule)\//i", '', $file);
        }

        return $files;
    }

    /**
     * Copy single stub file into module
     *
     * @param Module $module
     * @param string $stub_path
     * @param string $module_file
     * @param array  $replacements
     *
     * @throws Exception
     */
    protected function copyStubFileIntoModule(
        Module $module,
        $stub_path,
        $module_file,
        array $replacements = [])
    {
        if (!$this->exists($stub_path)) {
            throw new Exception("Stub file [{$stub_path}] does NOT exist");
        }

        // Create the name of the new module file
        $module_file = preg_replace('/\.stub$/i', '', $module_file);
        $module_file = $this->replace($module_file, $module, $replacements, '%:key:%');

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
