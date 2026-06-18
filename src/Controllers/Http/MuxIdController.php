<?php

// Examle request
// {
//     "type": "video.asset.ready",
//     "object": {
//       "type": "asset",
//       "id": "0201p02fGKPE7MrbC269XRD7LpcHhrmbu0002"
//     },
//     "id": "3a56ac3d-33da-4366-855b-f592d898409d",
//     "environment": {
//       "name": "Demo pages",
//       "id": "j0863n"
//     },
//     "data": {
//       "tracks": [
//         {
//           "type": "video",
//           "max_width": 1280,
//           "max_height": 544,
//           "max_frame_rate": 23.976,
//           "id": "0201p02fGKPE7MrbC269XRD7LpcHhrmbu0002",
//           "duration": 153.361542
//         },
//         {
//           "type": "audio",
//           "max_channels": 2,
//           "max_channel_layout": "stereo",
//           "id": "FzB95vBizv02bYNqO5QVzNWRrVo5SnQju",
//           "duration": 153.361497
//         }
//       ],
//       "status": "ready",
//       "max_stored_resolution": "SD",
//       "max_stored_frame_rate": 23.976,
//       "id": "0201p02fGKPE7MrbC269XRD7LpcHhrmbu0002",
//       "duration": 153.361542,
//       "created_at": "2018-02-15T01:04:45.000Z",
//       "aspect_ratio": "40:17"
//     },
//     "created_at": "2018-02-15T01:04:45.000Z",
//     "accessor_source": null,
//     "accessor": null,
//     "request_id": null
//   }

namespace ElSchneider\StatamicMuxId\Controllers\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Facades\Asset;

class MuxIdController extends Controller
{
    private const SIGNATURE_TOLERANCE = 300;

    private const SUPPORTED_EVENTS = [
        'video.asset.created',
        'video.asset.ready',
        'video.asset.errored',
        'video.asset.updated',
    ];

    public function update(Request $request): JsonResponse
    {
        if (! $this->hasValidSignature($request, config('statamic.mux-id.webhook_secret'))) {
            return new JsonResponse([
                'message' => 'Invalid webhook signature.',
            ], 401);
        }

        $type = $request->input('type');

        if (! is_string($type)) {
            return new JsonResponse([
                'message' => 'Missing webhook event type.',
            ], 400);
        }

        if (! in_array($type, self::SUPPORTED_EVENTS, true)) {
            return new JsonResponse(null, 204);
        }

        $muxAssetId = $request->input('object.id');

        if (! is_string($muxAssetId)) {
            return new JsonResponse([
                'message' => 'Missing Mux asset id.',
            ], 400);
        }

        $data = $request->input('data', []);

        if (! is_array($data)) {
            return new JsonResponse([
                'message' => 'Invalid Mux asset data.',
            ], 400);
        }

        // search assets and look for an asset, that has a field mux_data['id'] matching $muxAssetId
        $asset = Asset::all()->filter(function ($asset) use ($muxAssetId) {
            $data = $asset->get('mux_data');

            if (isset($data['id']) && $data['id'] === $muxAssetId) {
                return true;
            }
        })->first();

        // return if no asset was found with json response
        if (! $asset) {
            return new JsonResponse([
                'message' => 'No asset found with mux id '.$muxAssetId,
            ], 404);
        }

        $asset->set('mux_data', array_merge(
            (array) $asset->get('mux_data'),
            $data
        ));

        $asset->saveQuietly();

        return new JsonResponse(null, 204);
    }

    private function hasValidSignature(Request $request, ?string $secret): bool
    {
        if (empty($secret)) {
            return true;
        }

        $signatureHeader = $request->headers->get('mux-signature');

        if (empty($signatureHeader)) {
            return false;
        }

        $signatureParts = $this->parseSignatureHeader($signatureHeader);

        if (! isset($signatureParts['t'], $signatureParts['v1'])) {
            return false;
        }

        $timestamp = $signatureParts['t'];
        $signature = $signatureParts['v1'];

        if (! ctype_digit($timestamp) || ! ctype_xdigit($signature)) {
            return false;
        }

        if (abs(time() - (int) $timestamp) > self::SIGNATURE_TOLERANCE) {
            return false;
        }

        $signedPayload = $timestamp.'.'.$request->getContent();
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    private function parseSignatureHeader(string $signatureHeader): array
    {
        $signatureParts = [];

        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);

            if ($key && $value) {
                $signatureParts[$key] = $value;
            }
        }

        return $signatureParts;
    }
}
