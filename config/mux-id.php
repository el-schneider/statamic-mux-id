<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mux API Keys
    |--------------------------------------------------------------------------
    |
    | The Mux API keys are used to authenticate requests to the Mux Video API.
    | You can find these keys in your Mux account settings.
    |
     */

    'mux_token_id' => env('MUX_TOKEN_ID', ''),

    'mux_token_secret' => env('MUX_TOKEN_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Allowed filetype
    |--------------------------------------------------------------------------
    |
    | The allowed filetypes that will be processed by Mux.
    |
     */

    'allowed_filetypes' => [
        "mp4",
        "mov",
        "avi",
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Queue
    |--------------------------------------------------------------------------
    |
    | The name of the queue that will be used to process Mux asset creation
    | and status update jobs. Make sure the specified queue is configured
    | properly in your Laravel application.
    |
     */

    'queue' => env('STATAMIC_MUX_ID_FIELD_QUEUE', 'default'),

];
