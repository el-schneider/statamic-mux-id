<?php

namespace ElSchneider\StatamicMuxId;

use ElSchneider\StatamicMuxId\Controllers\Http\MuxIdController;
use ElSchneider\StatamicMuxId\GraphQL\MuxIdField;
use ElSchneider\StatamicMuxId\Listeners\AssetSavedListener;
use ElSchneider\StatamicMuxId\Listeners\AssetUploadedListener;
use ElSchneider\StatamicMuxId\Listeners\EnsureMuxMetadataField;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;
use Statamic\Events\AssetContainerBlueprintFound;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;
use Statamic\Facades\GraphQL;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $config = false;

    protected $listen = [
        AssetUploaded::class => [
            AssetUploadedListener::class,
        ],
        AssetSaved::class => [
            AssetSavedListener::class,
        ],
        AssetContainerBlueprintFound::class => [
            EnsureMuxMetadataField::class,
        ],
    ];

    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/mux-id.php', 'statamic.mux-id');
    }

    public function bootAddon(): void
    {
        $this->publishes([
            __DIR__.'/../config/mux-id.php' => config_path('statamic/mux-id.php'),
        ]);

        $this->registerActionRoutes(function () {
            Route::post('/listen', [MuxIdController::class, 'update'])->withoutMiddleware([ValidateCsrfToken::class]);
        });

        $this->bootGraphQL();
    }

    private function bootGraphQL(): void
    {
        GraphQL::addField('AssetInterface', 'mux_playback_id', function () {
            return (new MuxIdField)->toArray();
        });

    }
}
