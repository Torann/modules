<?php

namespace Torann\Modules\Traits;

use Torann\Modules\Config;
use Torann\Modules\Module;
use Illuminate\Support\Collection;

trait Replacer
{
    /**
     * Replace given string with default replacements and optionally user given
     *
     * @param string $string
     * @param Module $module
     * @param array $replacements
     *
     * @return string
     */
    protected function replace($string, Module $module, array $replacements = [])
    {
        $replacements = $this->getReplacements($module, $replacements);

        return str_replace($replacements->keys()->all(),
            $replacements->values()->all(), $string);
    }

    /**
     * Get replacement array that will be used for replace in string
     *
     * @param Module $module
     * @param array $definedReplacements
     *
     * @return Collection
     */
    private function getReplacements(Module $module, array $definedReplacements)
    {
        $replacements = new Collection();

        Collection::make([
            'module' => $module->name(),
            'class' => $module->name(),
            'moduleNamespace' => $module->name(),
            'namespace' => rtrim($this->config()->modulesNamespace(), '\\'),
            'plural|lower' => mb_strtolower(str_plural($module->name())),
        ])->merge($definedReplacements)
            ->each(function ($value, $key) use ($replacements) {
                $replacements->put("{{$key}}", $value);
            });

        return $replacements;
    }

    /**
     * Get config class instance
     *
     * @return Config
     */
    private function config()
    {
        return $this->laravel['modules.config'];
    }
}
