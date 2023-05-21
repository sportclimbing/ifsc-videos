<?php declare(strict_types=1);

const YOUTUBE_API_URL = 'https://www.googleapis.com/youtube/v3';
const IFSC_CHANNEL_ID = 'UC2MGuhIaOP6YLpUx106kTQw';
const DATABASE_FILE = __DIR__ . '/../data/videos.json';

function normalize_title(object $video): string
{
    return html_entity_decode($video->snippet->title, encoding: 'utf-8');
}

function remove_video_with_id(array $currentVideos, string $videoId): array
{
    return array_filter($currentVideos, static fn (object $video): bool => $video->video_id !== $videoId);
}

function duration_to_minutes(object $video): int
{
    $minutes = 0;

    if (preg_match('~^PT(?:(?<hours>\d)H)?(?:(?<minutes>\d{1,2})M)?(?:(?<seconds>\d{1,2})S)?$~', $video->contentDetails->duration, $duration)) {
        if (isset($duration['hours'])) {
            $minutes += ((int) $duration['hours']) * 60;
        }

        if (isset($duration['minutes'])) {
            $minutes += (int) $duration['minutes'];
        }

        if (isset($duration['seconds']) && $duration['seconds'] >= 30) {
            $minutes += 1;
        }
    }

    return $minutes;
}

function build_search_params(): string
{
    $params = [
        'key' => get_api_key(),
        'channelId' => IFSC_CHANNEL_ID,
        'part' => 'snippet',
        'type' => 'video',
        'order' => 'date',
        'regionCode' => 'US',
        'maxResults' => '50',
    ];

    return http_build_query($params, arg_separator: '&');
}


function build_video_details_params(string $videoId): string
{
    $params = [
        'key' => get_api_key(),
        'id' => $videoId,
        'part' => 'contentDetails',
    ];

    return http_build_query($params, arg_separator: '&');
}

function get_api_key(): string
{
    $apiKey = (string) getenv('YOUTUBE_API_KEY');

    if (!$apiKey) {
        echo 'Missing YouTube API key', PHP_EOL;
        exit(1);
    }

    return $apiKey;
}

function get_current_videos(): array
{
    return json_file(DATABASE_FILE);
}

function video_exists(array $currentVideos, string $videoId): bool
{
    $videos = array_filter(
        $currentVideos,
        static fn (object $video): bool => $video->video_id === $videoId
    );

    return count($videos) > 0;
}

function video_details(string $videoId, bool $useCache = true): object
{
    $fileName = sprintf('%s/../data/video/%s.json', __DIR__, $videoId);

    if (!file_exists($fileName) || !$useCache) {
        $response = get_contents_or_fail(
            build_api_video_details_url($videoId)
        );

        assert_is_json($response);

        file_put_contents($fileName, $response);
        $json = json_decode_or_fail($response);
    } else {
        $json = json_file($fileName);
    }

    return $json->items[0];
}

function video_by_id(array $oldVideos, string $videoId): ?object
{
    foreach ($oldVideos as $oldVideo) {
        if ($oldVideo->video_id === $videoId) {
            return $oldVideo;
        }
    }

    return null;
}

function assert_is_json(string $data)
{
    json_decode_or_fail($data);
}

function build_api_search_url(): string
{
    return sprintf(
        '%s/search?%s',
        YOUTUBE_API_URL,
        build_search_params(),
    );
}

function build_api_video_details_url(string $videoId): string
{
    return sprintf(
        '%s/videos?%s',
        YOUTUBE_API_URL,
        build_video_details_params($videoId),
    );
}

function fetch_recent_youtube_videos(): array
{
    return json_file(build_api_search_url())->items;
}

function get_contents_or_fail(string $url): string
{
    $contents = @file_get_contents($url);

    if (!is_string($contents)) {
        echo 'Unable to retrieve contents from URL', PHP_EOL;
        exit(1);
    }

    return $contents;
}

function json_decode_or_fail(string $data): object|array
{
    try {
        return json_decode($data, flags: JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        echo 'Invalid JSON: ', $e->getMessage(), PHP_EOL;
        exit(1);
    }
}

function json_file(string $file): object|array
{
    return json_decode_or_fail(
        get_contents_or_fail($file)
    );
}

function sort_by_date(array &$videos): void
{
    usort($videos, static fn (object $video1, object $video2): int => new DateTime($video2->published_at) <=> new DateTime($video1->published_at));
}
