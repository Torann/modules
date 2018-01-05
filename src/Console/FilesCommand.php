<?php

namespace Torann\Modules\Console;

use Torann\Modules\Module;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class FilesCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:files
                                {module : Module name}
                                {name* : Name (or multiple names space separated)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates new files structure in existing module.';

    /**
     * {@inheritdoc}
     */
    public function proceed()
    {
        $module_name = $this->argument('module');

        if (!($module = $this->laravel['modules']->find($module_name))) {
            $this->error("[Module {$module_name}] This module does not exist. Run <comment>module:make {$module_name}</comment> command first to create it");

            return;
        }

        $sub_modules = Collection::make($this->argument('name'))->unique();

        $sub_modules->each(function ($sub_module) use ($module) {
            $this->createSubModule($module, Str::studly($sub_module));
        });
    }

    /**
     * Create submodule for given module
     *
     * @param Module $module
     * @param string $sub_module
     */
    protected function createSubModule(Module $module, $sub_module)
    {
        // first create directories
        $this->createModuleDirectories($module);

        // now create files
        $status = $this->createModuleFiles($module, $sub_module, true);

        if ($status) {
            $this->info("[Module {$module->name()}] Submodule {$sub_module} was created.");
            $this->comment("You should register submodule routes (if any) into routes file for module {$module->name()}");
        }
        else {
            $this->warn("[Module {$module->name()}] Submodule {$sub_module} NOT created (all files already exist).");
        }
    }
}
