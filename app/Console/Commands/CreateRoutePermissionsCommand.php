<?php

namespace App\Console\Commands;

use App\Models\Module;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class CreateRoutePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:create-permission-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a permission routes.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $routes = Route::getRoutes()->getRoutes();

        foreach ($routes as $route) {
            $routeName = $route->getName();
              
            if (empty($routeName)) {
                continue;
            }
            $permission = Permission::firstOrCreate(['name' => $routeName]);
            $split = explode('.', $routeName);

            if (count($split) === 3) {
                $module = Module::where('slug', $split[1])->first();
                if ($module) {
                    $permission->update(['module_id' => $module->id]);
                }
            }
        }

        $this->info('Permission routes added successfully.');
    }
}
//php artisan permission:create-permission-routes 