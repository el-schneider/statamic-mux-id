<?php

namespace ElSchneider\StatamicMuxId\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MuxPhp\Api\AssetsApi;
use MuxPhp\ApiException;
use MuxPhp\Configuration;
use MuxPhp\Models\CreateAssetRequest;
use MuxPhp\Models\InputSettings;
use MuxPhp\Models\PlaybackPolicy;
use Statamic\Facades\Asset;

class CreateMuxAsset implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(private string $assetId) {}

    public function handle(): void
    {
        $asset = Asset::find($this->assetId);

        if (! $asset) {
            return;
        }

        $allowedFiletypes = config('statamic.mux-id.allowed_filetypes');

        if (! in_array(strtolower($asset->extension()), $allowedFiletypes, true)) {
            return;
        }

        $config = Configuration::getDefaultConfiguration()
            ->setUsername(config('statamic.mux-id.mux_token_id'))
            ->setPassword(config('statamic.mux-id.mux_token_secret'));

        $assetsApi = new AssetsApi(new Client, $config);

        $url = $asset->absoluteUrl();

        $input = new InputSettings(['url' => $url]);
        $createAssetRequest = new CreateAssetRequest([
            'inputs' => [$input],
            'playback_policies' => [PlaybackPolicy::_PUBLIC],
        ]);

        try {
            $response = $assetsApi->createAsset($createAssetRequest);
        } catch (ApiException $exception) {
            if (! $this->isPermanentMuxException($exception)) {
                throw $exception;
            }

            $asset->set('mux_data', array_merge($asset->get('mux_data') ?? [], [
                'status' => 'errored',
                'error' => $this->muxErrorData($exception),
            ]));
            $asset->saveQuietly();

            Log::warning('Mux asset creation failed permanently.', [
                'asset' => $this->assetId,
                'status' => $exception->getCode(),
                'error' => $this->muxErrorData($exception),
            ]);

            return;
        }

        $mux_data = [
            'playback_id' => $response->getData()->getPlaybackIds()[0]->getId(),
            'status' => $response->getData()->getStatus(),
            'id' => $response->getData()->getId(),
        ];

        $asset->set('mux_data', $mux_data);

        $asset->saveQuietly();
    }

    public function uniqueId(): string
    {
        return $this->assetId;
    }

    private function isPermanentMuxException(ApiException $exception): bool
    {
        return $exception->getCode() >= 400 && $exception->getCode() < 500;
    }

    private function muxErrorData(ApiException $exception): array
    {
        $responseBody = $exception->getResponseBody();
        $error = is_object($responseBody) && isset($responseBody->error) && is_object($responseBody->error)
            ? $responseBody->error
            : null;

        return [
            'code' => $exception->getCode(),
            'type' => $error->type ?? 'mux_api_error',
            'messages' => isset($error->messages) && is_array($error->messages)
                ? $error->messages
                : [$exception->getMessage()],
        ];
    }
}
