<?php

namespace Torann\Modules\Console;

use Torann\Modules\Module;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class MakeCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make 
                                {module* : Module name (or multiple module names space separated)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates new module structure.';

    /**
     * {@inheritdoc}
     */
    public function proceed()
    {
        $module_names = Collection::make($this->argument('module'))->unique();

        $module_names->each(function ($module_name) {

            $module = $this->createModuleObject($module_name);

            if ($this->laravel['modules']->exists($module->name())
                || $this->exists($module->directory())
            ) {
                $this->warn("[Module {$module->name()}] Module already exists - ignoring");
            }
            else {
                $this->createModule($module);

                $this->info("[Module {$module->name()}] Module was generated");
            }
        });
    }

    /**
     * Create module object (it does not mean module exists)
     *
     * @param string $module_name
     *
     * @return Module
     */
    protected function createModuleObject($module_name)
    {
        return new Module(Str::studly($module_name), $this->laravel);
    }

    /**
     * Create module
     *
     * @param Module $module
     */
    protected function createModule(Module $module)
    {
        $this->createModuleFiles($module);

        // Finally add module to configuration (if not disabled in config)
        $this->addModuleToConfigurationFile($module);
    }

    /**
     * Add module to configuration file
     *
     * @param $module
     */
    protected function addModuleToConfigurationFile(Module $module)
    {
        $config_file = $this->laravel['modules.config']->configPath();

        // Getting modified content of config file
        $result =
            preg_replace_callback('/(\'modules\'\s*=>\s*\[\s*)(.*)/m',
                function ($matches) use ($module, $config_file) {

                    // Remove all whitespace
                    $prefix = trim($matches[1]);
                    $suffix = trim($matches[2]);

                    return "{$prefix}"
                        . $this->replace(
                            "\n        '{class}' => [\n".
                            "            'active' => true,\n".
                            "            'routes' => true,\n".
                            "        ],\n",
                            $module)
                        . ($suffix[0] !== ']' ? '    ' : '') // This keeps everything in tabbed correctly
                        . "    {$suffix}";
                },
                $this->files->get($config_file), -1, $count);

        if ($count) {
            // Found place where new module should be added into config file
            $this->files->put($config_file, $result);
            $this->comment("[Module {$module->name()}] Added into config file {$config_file}");
        }
        else {
            // Cannot add module to config file automatically
            $this->warn("[Module {$module->name()}] It was impossible to add module into {$config_file}" .
                " file.\n Please make sure you haven't changed structure of this file. " .
                "At the moment add <info>{$module->name()}</info> to this file manually");
        }
    }
}
