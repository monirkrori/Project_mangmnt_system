<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name}';
    protected $description = 'Create a new service class';

    public function handle()
    {
        $name = $this->argument('name');
        $serviceName = ucfirst($name);
        $path = app_path("Services/{$serviceName}.php");

        if (File::exists($path)) {
            $this->error("Service {$serviceName} already exists.");
            return;
        }

        $stub = file_get_contents(base_path('stubs/service.stub'));

        $stub = str_replace('{{ serviceName }}', $serviceName, $stub);

        File::ensureDirectoryExists(app_path('Services'));
        File::put($path, $stub);

        $this->components->info("Service [{$path}] created successfully.");
    }
}
