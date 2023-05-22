<?php

namespace ElSchneider\StatamicMuxId\Listeners;

use ElSchneider\StatamicMuxId\Jobs\CreateMuxAsset;
use Statamic\Events\AssetSaved;

class AssetSavedListener
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
     * @param  \Statamic\Events\AssetSaved  $event
     * @return void
     */
    public function handle(AssetSaved $event)
    {
        $asset = $event->asset;

        // check is asset has a playback id
        if (isset($asset->get('mux_data')['playback_id'])) {
            return;
        }

        CreateMuxAsset::dispatch($asset)
            ->onQueue(config('mux-id.queue'));
    }
}
