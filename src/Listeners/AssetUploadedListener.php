<?php

namespace ElSchneider\StatamicMuxId\Listeners;

use ElSchneider\StatamicMuxId\Jobs\CreateMuxAsset;
use Statamic\Events\AssetUploaded;

class AssetUploadedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Statamic\Events\AssetUploaded  $event
     * @return void
     */
    public function handle(AssetUploaded $event)
    {
        // dispatch job to create mux asset
        $asset = $event->asset;

        CreateMuxAsset::dispatch($asset)
            ->onQueue(config('mux-id.queue'));
    }
}
