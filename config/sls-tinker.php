<?php

return [
    /**
     * Use to custom lambda endpoint.
     * Leave it empty if you are using the default AWS Lambda endpoint.
     * If you are using a custom endpoint, set it to the URL of your custom endpoint
     * Example: 'http://localhost:9090'
     */
    'lambda_endpoint' => env('SLS_TINKER_LAMBDA_ENDPOINT', ''),

    /**
     * Use to set the platform for SLS Tinker.
     * The platform can be 'bref' or 'vapor'.
     * - 'bref' is used for AWS Lambda with Bref.
     * - 'vapor' is used for Laravel Vapor.
     * If you are using AWS Lambda with Bref, you can leave this as 'bref'.
     * If you are using a different platform or local development, set this to 'vapor'
     */
    'platform' => env('SLS_PLATFORM', 'bref'),
];
