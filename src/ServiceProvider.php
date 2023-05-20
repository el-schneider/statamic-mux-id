<?php

namespace ElSchneider\StatamicMuxId;

use ElSchneider\StatamicMuxId\Controllers\Http\MuxIdController;
use Illuminate\Support\Facades\Route;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $listen = [
        'Statamic\Events\AssetUploaded' => [
            'ElSchneider\StatamicMuxId\Listeners\AssetUploadedListener',
        ],
    ];

    public function bootAddon()
    {
        $this->publishes([
            __DIR__ . '/../config/mux-id.php' => config_path('statamic/mux-id.php'),
        ]);

        $this->registerActionRoutes(function () {
            Route::post("/listen", [MuxIdController::class, 'update']);
        });
    }
}
