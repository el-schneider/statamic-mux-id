<?php

namespace ElSchneider\StatamicMuxId\GraphQL;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Field;
use Statamic\Assets\Asset;
use Statamic\Facades\GraphQL;

class MuxIdField extends Field
{

    public function type(): Type
    {
        return GraphQL::string();
    }

    protected function resolve(Asset $asset, array $args)
    {
        $mux_data = $asset->get('mux_data');

        if (!isset($mux_data)) {
            return null;
        }

        return $mux_data['playback_id'];
    }
}
