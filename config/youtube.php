<?php

return [
    /*
    |--------------------------------------------------------------------------
    | YouTube API Key
    |--------------------------------------------------------------------------
    |
    | This value is the API key used to authenticate requests to the YouTube
    | Data API v3. Define this in your .env file.
    |
    */
    'api_key' => env('YOUTUBE_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | YouTube API Endpoints
    |--------------------------------------------------------------------------
    |
    | Define the base URLs and specific API endpoints used by the application
    | to fetch data from YouTube.
    |
    */
    'endpoints' => [
        'search' => 'https://www.googleapis.com/youtube/v3/search',
        'playlist_items' => 'https://www.googleapis.com/youtube/v3/playlistItems',
        'videos' => 'https://www.googleapis.com/youtube/v3/videos',
        
        // Base link for redirecting users to the actual YouTube playlist in the browser
        'playlist_url' => 'https://www.youtube.com/playlist?list=',
    ],
];
