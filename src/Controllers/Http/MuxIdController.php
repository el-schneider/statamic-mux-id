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

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MuxIdController extends Controller
{

    private $events_list = [
        'video.asset.created',
        'video.asset.ready',
        'video.asset.errored',
        'video.asset.updated',
    ];

    public function update(Request $request)
    {
        // get the event type
        $type = $request->type;

        // check if the event type is in the list of events we want to listen for
        if (!in_array($type, $this->events_list)) {
            return;
        }

        // get the mux asset id from the request
        $muxAssetId = $request->object['id'];

        // search assets and look for an asset, that has a field mux_data['id'] matching $muxAssetId
        $asset = \Statamic\Facades\Asset::all()->filter(function ($asset) use ($muxAssetId) {
            $data = $asset->get('mux_data');

            if (isset($data['id']) && $data['id'] === $muxAssetId) {
                return true;
            }
        })->first();

        // return if no asset was found with json response
        if (!$asset) {
            return response()->json([
                'message' => 'No asset found with mux id ' . $muxAssetId,
            ], 404);
        }

        // update mux_data of the asset with the reponse
        $merged_data = array_merge($asset->get("mux_data"), $request->data);
        $asset->set('mux_data', $merged_data);

        // save the asset
        $asset->saveQuietly();
    }
}
