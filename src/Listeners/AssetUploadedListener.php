<?php

namespace ElSchneider\StatamicMuxId\Listeners;

use ElSchneider\StatamicMuxId\Jobs\CreateMuxAsset;
use Statamic\Events\AssetUploaded;

class AssetUploadedListener
{
    public function handle(AssetUploaded $event): void
    {
        CreateMuxAsset::dispatch($event->asset->id())
            ->onQueue(config('statamic.mux-id.queue'));
    }
}
