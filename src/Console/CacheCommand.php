<?php

namespace Torann\Modules\Console;

class CacheCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a module cache file for faster module registration';

    /**
     * {@inheritdoc}
     */
    public function proceed()
    {
        $this->call('module:clear');

        $manifest = [
            'service_providers' => $this->laravel['modules']->withServiceProviders()->all(),
        ];

        $this->files->put(
            $this->laravel['modules']->getCachedPath(), '<?php return ' . var_export($manifest, true) . ';'
        );

        $this->info('Modules cached successfully!');
    }
}
