<?php

if (!function_exists('modules')) {
    /**
     * Get the module manager instance.
     *
     * @return \Torann\Modules\ModuleManager
     */
    function modules()
    {
        return app('modules');
    }
}