<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeInterface extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:interface {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new interface class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $interfaceDirectory = app_path('Interfaces');
        $interfacePath = $interfaceDirectory . '/' . $name . '.php';

        if (!File::exists($interfaceDirectory)) {
            File::makeDirectory($interfaceDirectory, 0755, true);
        }

        if (File::exists($interfacePath)) {
            $this->error('Interface already exists!');
            return false;
        }

        $interfaceContent = "<?php\n\nnamespace App\Interfaces;\n\ninterface $name\n{\n    // Define your interface methods here\n}\n";

        File::put($interfacePath, $interfaceContent);

        $this->info('Interface created successfully!');
    }
}
