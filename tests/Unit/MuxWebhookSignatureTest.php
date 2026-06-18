<?php

use ElSchneider\StatamicMuxId\Controllers\Http\MuxIdController;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Http\Request;

afterEach(function () {
    Container::setInstance(null);
});

test('unsigned webhooks are allowed when no secret is configured', function () {
    $request = requestWithPayload('{"type":"video.asset.ready"}');

    expect(hasValidSignature($request, ''))->toBeTrue();
});

test('valid mux signature is accepted', function () {
    $secret = 'webhook-secret';
    $payload = '{"type":"video.asset.ready"}';
    $timestamp = (string) time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
    $request = requestWithPayload($payload, "t={$timestamp},v1={$signature}");

    expect(hasValidSignature($request, $secret))->toBeTrue();
});

test('invalid mux signature is rejected', function () {
    $payload = '{"type":"video.asset.ready"}';
    $timestamp = (string) time();
    $request = requestWithPayload($payload, "t={$timestamp},v1=deadbeef");

    expect(hasValidSignature($request, 'webhook-secret'))->toBeFalse();
});

test('missing mux signature is rejected when secret is configured', function () {
    $request = requestWithPayload('{"type":"video.asset.ready"}');

    expect(hasValidSignature($request, 'webhook-secret'))->toBeFalse();
});

test('update returns 401 for invalid signature when secret is configured', function () {
    $container = new Container;
    $config = new Repository;
    $config->set('statamic.mux-id.webhook_secret', 'webhook-secret');
    $container->instance('config', $config);
    Container::setInstance($container);

    $payload = '{"type":"video.asset.ready"}';
    $timestamp = (string) time();
    $request = requestWithPayload($payload, "t={$timestamp},v1=deadbeef");
    $response = (new MuxIdController)->update($request);

    expect($response->getStatusCode())->toBe(401);
});

test('stale mux signature is rejected', function () {
    $secret = 'webhook-secret';
    $payload = '{"type":"video.asset.ready"}';
    $timestamp = (string) (time() - 301);
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
    $request = requestWithPayload($payload, "t={$timestamp},v1={$signature}");

    expect(hasValidSignature($request, $secret))->toBeFalse();
});

function requestWithPayload(string $payload, ?string $signatureHeader = null): Request
{
    $server = [];

    if ($signatureHeader) {
        $server['HTTP_MUX_SIGNATURE'] = $signatureHeader;
    }

    return Request::create('/!/statamic-mux-id/listen', 'POST', [], [], [], $server, $payload);
}

function hasValidSignature(Request $request, ?string $secret): bool
{
    $method = (new ReflectionClass(MuxIdController::class))->getMethod('hasValidSignature');

    return $method->invoke(new MuxIdController, $request, $secret);
}
