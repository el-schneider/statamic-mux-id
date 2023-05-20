<?php

namespace ElSchneider\StatamicMuxId\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MuxPhp\Api\AssetsApi;
use MuxPhp\Api\PlaybackIDApi;
use MuxPhp\Configuration;
use MuxPhp\Models\CreateAssetRequest;
use MuxPhp\Models\InputSettings;
use MuxPhp\Models\PlaybackPolicy;

class CreateMuxAsset implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    private $config;

    protected $asset;

    public function __construct($asset)
    {
        $this->asset = $asset;

        $this->config = Configuration::getDefaultConfiguration()
            ->setUsername(config('statamic.mux-id.mux_token_id'))
            ->setPassword(config('statamic.mux-id.mux_token_secret'));
    }

    public function handle()
    {
        $allowed_filestypes = config('statamic.mux-id.allowed_filetypes');

        if (!in_array($this->asset->extension(), $allowed_filestypes)) {
            return;
        }

        $assetsApi = new AssetsApi(new Client(), $this->config);
        $playbackIdApi = new PlaybackIDApi(new Client(), $this->config);

        // determine the url based on environment
        $url = $this->asset->permalink();

        $input = new InputSettings(["url" => $url]);
        $createAssetRequest = new CreateAssetRequest([
            "input" => [$input],
            "playback_policy" => [PlaybackPolicy::_PUBLIC],
        ]);

        $response = $assetsApi->createAsset($createAssetRequest);

        $mux_data = [
            'playback_id' => $response->getData()->getPlaybackIds()[0]->getId(),
            'status' => $response->getData()->getStatus(),
            'id' => $response->getData()->getId(),
        ];

        $this->asset->set('mux_data', $mux_data);

        $this->asset->saveQuietly();
    }
}
