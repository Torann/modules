<?php

namespace Torann\Modules\Console\Traits;

use Exception;
use Torann\Modules\Module;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

trait ModuleVerification
{
    /**
     * Verify whether given modules exist and are active
     *
     * @param Collection $moduleNames
     *
     * @return Collection
     * @throws Exception
     */
    protected function verifyActive(Collection $moduleNames)
    {
        $modules = $this->verifyModules($moduleNames, true);

        if ($modules->count() != $moduleNames->count()) {
            throw new Exception('There were errors. You need to pass only valid active module names');
        }

        return $modules;
    }

    /**
     * Verify whether given modules exist
     *
     * @param Collection $moduleNames
     *
     * @return Collection
     * @throws Exception
     */
    protected function verifyExisting(Collection $moduleNames)
    {
        $modules = $this->verifyModules($moduleNames, false);

        if ($modules->count() != $moduleNames->count()) {
            throw new Exception('There were errors. You need to pass only valid module names');
        }

        return $modules;
    }

    /**
     * Verifies whether given modules exist and whether they are active
     *
     * @param Collection $moduleNames
     * @param bool       $verifyActive
     *
     * @return Collection|bool
     */
    private function verifyModules(Collection $moduleNames, $verifyActive)
    {
        $modules = new Collection();

        $moduleNames->each(function ($name) use ($verifyActive, $modules) {

            $name = Str::studly($name);

            /** @var Module $module */
            $module = $this->laravel['modules']->find($name);

            if (!$module) {
                $this->error("Module {$name} does not exist");

                return;
            }

            if ($verifyActive && !$module->active()) {
                $this->error("Module {$name} is not active");

                return;
            }

            $modules->push($module);
        });

        return $modules;
    }
}
