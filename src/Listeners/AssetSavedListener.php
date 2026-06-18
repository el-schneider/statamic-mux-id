<?php

namespace ElSchneider\StatamicMuxId\Listeners;

use ElSchneider\StatamicMuxId\Jobs\CreateMuxAsset;
use Statamic\Events\AssetSaved;

class AssetSavedListener
{
    public function handle(AssetSaved $event): void
    {
        $asset = $event->asset;
        $muxData = $asset->get('mux_data');

        if (isset($muxData['id']) || isset($muxData['playback_id'])) {
            return;
        }

        CreateMuxAsset::dispatch($asset->id())
            ->onQueue(config('statamic.mux-id.queue'));
    }
}
