<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class LoadHelpersProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (App::runningInConsole()) {
            return;
        }
        
        $this->handle();
    }

    private function handle()
    {
        require_once app_path('Helpers/CondominiumHelper.php');
    }
}
