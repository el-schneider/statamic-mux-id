<?php

namespace ElSchneider\StatamicMuxId\Listeners;

use Statamic\Events\AssetContainerBlueprintFound;

class EnsureMuxMetadataField
{
    public function handle(AssetContainerBlueprintFound $event): void
    {
        $event->blueprint->ensureField('mux_data', [
            'type' => 'yaml',
            'display' => 'Mux Metadata',
            'instructions' => 'Mux asset metadata synced by Statamic Mux Id.',
            'visibility' => 'read_only',
        ]);
    }
}
