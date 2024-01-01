<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $repositoryDirectory = app_path('Repositories');
        $repositoryPath = $repositoryDirectory . '/' . $name . '.php';

        if (!File::exists($repositoryDirectory)) {
            File::makeDirectory($repositoryDirectory, 0755, true);
        }

        if (File::exists($repositoryPath)) {
            $this->error('Repository already exists!');
            return false;
        }

        $repositoryContent = "<?php\n\nnamespace App\Repositories;\n\nclass $name\n{\n    // Define your repository methods here\n}\n";

        File::put($repositoryPath, $repositoryContent);

        $this->info('Repository created successfully!');
    }
}
