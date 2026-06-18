<?php

namespace ElSchneider\StatamicMuxId\Listeners;

use Statamic\Events\AssetContainerBlueprintFound;

class EnsureMuxMetadataField
{
    public function handle(AssetContainerBlueprintFound $event): void
    {
        if ($event->blueprint->hasField('mux_data')) {
            return;
        }

        $event->blueprint
            ->ensureField('show_mux_metadata', [
                'type' => 'revealer',
                'display' => 'Mux Metadata',
                'instructions' => 'Show Mux asset metadata synced by Statamic Mux Id.',
                'mode' => 'button',
                'input_label' => 'Show Mux Metadata',
            ])
            ->ensureField('mux_data', [
                'type' => 'yaml',
                'display' => 'Mux Metadata',
                'instructions' => 'Mux asset metadata synced by Statamic Mux Id.',
                'visibility' => 'read_only',
                'if' => [
                    'show_mux_metadata' => true,
                ],
            ]);
    }
}
