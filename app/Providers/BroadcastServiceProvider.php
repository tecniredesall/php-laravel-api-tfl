<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot()
    {
        // $attributes = $attributes ?: ['middleware' => ['api']];
        Broadcast::routes( ['middleware' => ['auth:api']] );

        require base_path('routes/channels.php');
    }
}
