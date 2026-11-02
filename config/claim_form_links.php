<?php

return [
    // Trusted configured origin, including /kvmgmt when deployed in a subfolder.
    // Never build invitation emails from a browser-supplied Host header.
    'base_url' => env('CLAIM_FORM_BASE_URL', env('APP_URL', 'http://localhost')),
    'expires_days' => (int) env('CLAIM_FORM_LINK_EXPIRES_DAYS', 7),
    'max_items' => 25,
];
