<?php

namespace Torann\Modules\Console;

use Exception;
use Torann\Modules\Module;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Torann\Modules\Console\Traits\ModuleVerification;

class MigrationCommand extends AbstractCommand
{
    use ModuleVerification;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:migration
                                {module : Module name}
                                {name : Migration full name (ex. create_users_table)}
                                {--type= : Type of migration (default options: create, edit)}
                                {--table= : Table name (use with --type)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create migration in selected module';

    /**
     * {@inheritdoc}
     */
    public function proceed()
    {
        $module = $this->argument('module');
        $name = $this->argument('name');
        $type = $this->option('type');
        $table = $this->option('table');

        // verify whether both type and table used
        if ($type && !$table || $table && !$type) {
            throw new Exception('You need to use both options --type and --table when using any of them');
        }

        // verify whether module exists
        $modules = $this->verifyExisting(Collection::make((array)$module));

        $this->createMigrationFile($modules->first(), $name, $type, $table);
    }

    /**
     * Create migration file
     *
     * @param Module $module
     * @param string $name
     * @param string $type
     * @param string $table
     *
     * @throws Exception
     */
    protected function createMigrationFile(Module $module, $name, $type, $table)
    {
        // Get stub file path
        $stub_file = $this->laravel['modules']->stubsPath(
            'migrations/migration' . ($type ? '_' : '') . $type . '.php.stub'
        );

        // Validate stub file
        if (file_exists($stub_file) === false) {
            throw new Exception("There is no [{$type}] type for migrations");
        }

        // Migration file name
        $filename = $this->getMigrationFileName($name);

        $this->copyStubFileIntoModule($module, $stub_file,
            $module->migrationsPath(true) . DIRECTORY_SEPARATOR . $filename, [
                'migrationClass' => Str::studly($name),
                'table' => $table,
            ]
        );

        $this->info("[Module {$module->name()}] Created Migration: {$filename}");
    }

    /**
     * Get migration file name based on user given migration name
     *
     * @param string $name
     *
     * @return string
     */
    protected function getMigrationFileName($name)
    {
        return date('Y_m_d_His') . '_' . Str::snake($name) . '.php';
    }
}
