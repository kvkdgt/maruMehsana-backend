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
        'scheme' => 'https',
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
    | External "add news" API (POST /api/external/news).
    | The key is sent by callers in the X-API-Key header and checked against the
    | SHA-256 hash below (or a plain NEWS_API_KEY in .env, if one is set).
    */
    'news_api' => [
        // Optional plain key from .env (overrides nothing; either key style is accepted).
        'key'             => env('NEWS_API_KEY'),
        // Static key: only the SHA-256 hash is stored here, so the repository never contains the real key.
        'key_sha256'      => env('NEWS_API_KEY_SHA256', 'bc86aa2226173b216d79a231c622a59ef3f17e300f2f6d00f2edc0de9b4cb16c'),
        'agency_username' => env('NEWS_API_AGENCY_USERNAME', 'MaruMehsana'),
    ],

    /*
    | Password-protected "upload news" web page (/news-upload).
    | Only the SHA-256 hash of the password is stored, never the password itself.
    */
    'news_upload' => [
        'password_sha256' => env('NEWS_UPLOAD_PASSWORD_SHA256', '3902b8f0203db5d6f92cfe7edf1322ea82c93f9df66995b1fef0bcc932b566c0'),
    ],

    /*
    | New business enquiry ("lead") alert e-mail. Comma-separated list allowed.
    | Sent for every enquiry, from the app and from the website popup.
    */
    'leads' => [
        'notify_email' => env('LEAD_NOTIFY_EMAIL', 'kvkdgt12345@gmail.com'),
    ],

];
