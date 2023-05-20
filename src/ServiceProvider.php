<?php

namespace ElSchneider\StatamicMuxId;

use ElSchneider\StatamicMuxId\Controllers\Http\MuxIdController;
use ElSchneider\StatamicMuxId\GraphQL\MuxIdField;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\GraphQL;
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

        $this->bootGraphQL();
    }

    private function bootGraphQL(): self
    {
        GraphQL::addField('AssetInterface', 'mux_playback_id', function () {
            return (new MuxIdField())->toArray();
        });

        return $this;
    }
}
