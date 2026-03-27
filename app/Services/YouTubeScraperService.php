<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YouTubeScraperService
{
    /**
     * @param string $courseTitle
     * @return array
     */
    public function searchPlaylists(string $courseTitle): array
    {
        $apiKey = config('youtube.api_key');

        if (empty($apiKey)) {
            \Log::warning('YouTube API key is missing. Skipping search.');
            return [];
        }

        // Delay to prevent hitting Google burst rate limits
        usleep(500000); // half a second

        // Retry automatically up to 3 times on server errors or 429 Rate Limits
        $response = Http::retry(3, 1000, function ($exception, $request) {
            return $exception->response && $exception->response->status() === 429;
        })->get(config('youtube.endpoints.search'), [
            'part' => 'snippet',
            'type' => 'playlist',
            'q' => $courseTitle,
            'maxResults' => 2, // As requested, we strictly enforce only 2 playlists per course
            'key' => $apiKey,
        ]);

        if ($response->failed()) {
            \Log::error('YouTube API search failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return [];
        }

        $items = $response->json('items', []);
        $playlists = [];

        foreach ($items as $item) {
            $playlistId = $item['id']['playlistId'];
            $metaData = $this->getPlaylistMetaData($playlistId, $apiKey);

            $playlists[] = [
                'playlist_id' => $playlistId,
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'],
                'thumbnail' => $item['snippet']['thumbnails']['high']['url'] ?? ($item['snippet']['thumbnails']['default']['url'] ?? null),
                'channel_name' => $item['snippet']['channelTitle'],
                'lessons_count' => $metaData['lessons_count'],
                'view_count' => $metaData['view_count'],
                'duration_seconds' => $metaData['duration_seconds'],
            ];
        }

        return $playlists;
    }

    private function getPlaylistMetaData(string $playlistId, string $apiKey): array
    {
        $metaData = ['lessons_count' => 0, 'view_count' => 0, 'duration_seconds' => 0];

        // API Limiting explicit delay
        usleep(500000); 

        $itemsResponse = Http::retry(3, 1000, function($e, $r) {
            return $e->response && $e->response->status() === 429;
        })->get(config('youtube.endpoints.playlist_items'), [
            'part' => 'contentDetails',
            'playlistId' => $playlistId,
            'maxResults' => 50,
            'key' => $apiKey,
        ]);

        if ($itemsResponse->failed()) {
            return $metaData;
        }

        // Use totalResults for total item count, as maxResults=50 might truncate
        $metaData['lessons_count'] = $itemsResponse->json('pageInfo.totalResults', count($itemsResponse->json('items', [])));

        $itemsData = $itemsResponse->json('items', []);
        if (empty($itemsData)) return $metaData;

        $videoIds = collect($itemsData)->pluck('contentDetails.videoId')->filter()->toArray();

        usleep(500000); 

        // 2. Fetch video statistics and contentDetails for duration/views
        $videosResponse = Http::retry(3, 1000, function($e, $r) {
            return $e->response && $e->response->status() === 429;
        })->get(config('youtube.endpoints.videos'), [
            'part' => 'statistics,contentDetails',
            'id' => implode(',', $videoIds),
            'key' => $apiKey,
        ]);

        if ($videosResponse->failed()) return $metaData;

        $videosData = $videosResponse->json('items', []);
        $totalViews = 0;
        $totalDuration = 0;

        foreach ($videosData as $video) {
            $totalViews += (int) ($video['statistics']['viewCount'] ?? 0);
            
            if (isset($video['contentDetails']['duration'])) {
                $totalDuration += $this->parseIsoDuration($video['contentDetails']['duration']);
            }
        }

        $metaData['view_count'] = $totalViews;
        $metaData['duration_seconds'] = $totalDuration;

        return $metaData;
    }

    private function parseIsoDuration(string $duration): int
    {
        try {
            $interval = new \DateInterval($duration);
            return ($interval->d * 86400) + ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
