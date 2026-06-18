<?php

use ElSchneider\StatamicMuxId\Listeners\EnsureMuxMetadataField;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Statamic\Events\AssetContainerBlueprintFound;
use Statamic\Fields\Blueprint;
use Statamic\Support\Blink;

afterEach(function () {
    Facade::clearResolvedInstances();
    Facade::setFacadeApplication(null);
    Container::setInstance(null);
});

test('mux metadata field is hidden behind a revealer on asset blueprints', function () {
    bootstrapStatamicFacades();

    $blueprint = (new Blueprint)->setContents([
        'tabs' => [
            'main' => [
                'sections' => [[
                    'fields' => [
                        [
                            'handle' => 'alt',
                            'field' => [
                                'type' => 'text',
                                'display' => 'Alt Text',
                            ],
                        ],
                    ],
                ]],
            ],
        ],
    ]);

    (new EnsureMuxMetadataField)->handle(new AssetContainerBlueprintFound($blueprint));

    $fields = $blueprint->contents()['tabs']['main']['sections'][0]['fields'];

    expect($fields)->toHaveCount(3)
        ->and($fields[1]['handle'])->toBe('show_mux_metadata')
        ->and($fields[1]['field'])->toMatchArray([
            'type' => 'revealer',
            'display' => 'Mux Metadata',
            'mode' => 'button',
            'input_label' => 'Show Mux Metadata',
        ])
        ->and($fields[2]['handle'])->toBe('mux_data')
        ->and($fields[2]['field'])->toMatchArray([
            'type' => 'yaml',
            'display' => 'Mux Metadata',
            'visibility' => 'read_only',
            'if' => [
                'show_mux_metadata' => true,
            ],
        ]);
});

test('existing mux metadata field config wins and no revealer is added', function () {
    bootstrapStatamicFacades();

    $blueprint = (new Blueprint)->setContents([
        'tabs' => [
            'main' => [
                'sections' => [[
                    'fields' => [
                        [
                            'handle' => 'mux_data',
                            'field' => [
                                'type' => 'hidden',
                                'display' => 'Custom Mux Data',
                            ],
                        ],
                    ],
                ]],
            ],
        ],
    ]);

    (new EnsureMuxMetadataField)->handle(new AssetContainerBlueprintFound($blueprint));

    $fields = $blueprint->contents()['tabs']['main']['sections'][0]['fields'];

    expect($fields)->toHaveCount(1)
        ->and($fields[0]['field']['type'])->toBe('hidden')
        ->and($fields[0]['field']['display'])->toBe('Custom Mux Data');
});

function bootstrapStatamicFacades(): void
{
    $container = new Container;
    $container->instance(Blink::class, new Blink);

    Container::setInstance($container);
    Facade::setFacadeApplication($container);
}
