<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging
    |--------------------------------------------------------------------------
    | Per-project credentials live in the firebase_projects DB table (managed
    | from the Notify module). These keys are only Android presentation
    | defaults shared across all apps; override via .env if needed.
    */
    'firebase' => [
        // Android small-icon drawable name. Leave EMPTY by default: sending a
        // name that has no matching res/drawable/<name> makes some Android
        // versions fail to render the notification. Empty = FCM falls back to
        // the app's manifest launcher icon, which always works.
        'android_small_icon' => env('FCM_SMALL_ICON', ''),
        // Notification channel id the app registered. Leave empty to let
        // Android use its default channel (an unknown id is silently dropped).
        'android_channel_id' => env('FCM_CHANNEL_ID'),
        // Optional accent colour (hex "#RRGGBB") behind the small icon.
        'android_icon_color' => env('FCM_ICON_COLOR'),
    ],

];
