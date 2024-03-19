<?php declare(strict_types=1);

/**
 * @license  http://opensource.org/licenses/mit-license.php MIT
 * @link     https://github.com/nicoSWD
 * @author   Nicolas Oelgart <nico@oelgart.com>
 */
namespace nicoSWD\IfscVideos\Infrastructure\YouTube;

use DateTimeImmutable;
use nicoSWD\IfscVideos\Domain\YouTube\YouTubeVideo;
use nicoSWD\IfscVideos\Domain\YouTube\YouTubeVideoProviderInterface;
use Override;

final readonly class YouTubeVideoProvider implements YouTubeVideoProviderInterface
{
    /** @inheritdoc */
    #[Override]
    public function getAllVideos(): array
    {
        $videos = [];

        foreach ($this->importVideosFromFile() as $video) {
            $videos[] = $this->createVideo($video);
        }

        return $videos;
    }

    /** @inheritdoc */
    #[Override]
    public function fetchRestrictedRegionsForVideo(string $videoId): array
    {
        $videoDetails = $this->loadVideoDetails($videoId);

        if ($videoDetails) {
            return $videoDetails->items[0]?->contentDetails?->regionRestriction?->blocked ?? [];
        }

        return [];
    }

    private function createVideo(object $video): YouTubeVideo
    {
        return new YouTubeVideo(
            videoId: $video->video_id,
            title: $video->title,
            publishedAt: new DateTimeImmutable($video->published_at),
            duration: $video->duration,
            restrictedRegions: $video->restricted_regions,
        );
    }

    private function loadJsonFile(string $path): array|object
    {
        return json_decode(file_get_contents($path));
    }

    private function importVideosFromFile(): array
    {
        return $this->loadJsonFile(__DIR__ . '/../../../data/videos.json');
    }

    private function loadVideoDetails(string $videoId): array|object|null
    {
        $videoFile = __DIR__ . "/../../../data/video/{$videoId}.json";

        if (is_file($videoFile)) {
            return $this->loadJsonFile($videoFile);
        }

        return null;
    }
}
