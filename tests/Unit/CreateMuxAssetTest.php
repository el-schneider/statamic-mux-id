<?php

use ElSchneider\StatamicMuxId\Jobs\CreateMuxAsset;
use MuxPhp\ApiException;

test('client side mux api exceptions are permanent', function () {
    $job = new CreateMuxAsset('assets::video.mp4');
    $exception = new ApiException('Unauthorized', 401);

    expect(invokeCreateMuxAssetMethod($job, 'isPermanentMuxException', $exception))->toBeTrue();
});

test('server side mux api exceptions are retried', function () {
    $job = new CreateMuxAsset('assets::video.mp4');
    $exception = new ApiException('Server error', 500);

    expect(invokeCreateMuxAssetMethod($job, 'isPermanentMuxException', $exception))->toBeFalse();
});

test('mux api exception response is stored as asset metadata', function () {
    $job = new CreateMuxAsset('assets::video.mp4');
    $body = (object) [
        'error' => (object) [
            'type' => 'unauthorized',
            'messages' => ['This action must be completed through the dashboard interface.'],
        ],
    ];
    $exception = new ApiException('Unauthorized', 401, [], $body);

    expect(invokeCreateMuxAssetMethod($job, 'muxErrorData', $exception))->toBe([
        'code' => 401,
        'type' => 'unauthorized',
        'messages' => ['This action must be completed through the dashboard interface.'],
    ]);
});

function invokeCreateMuxAssetMethod(CreateMuxAsset $job, string $method, mixed ...$arguments): mixed
{
    $method = (new ReflectionClass(CreateMuxAsset::class))->getMethod($method);

    return $method->invoke($job, ...$arguments);
}
