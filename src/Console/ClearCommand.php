<?php

namespace Torann\Modules\Console;

class ClearCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the module cache file';

    /**
     * {@inheritdoc}
     */
    public function proceed()
    {
        $this->files->delete($this->laravel['modules']->getCachedPath());

        $this->laravel['modules']->clearCache();

        $this->info('Module cache cleared!');
    }
}
