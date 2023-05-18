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

final readonly class YouTubeVideoProvider implements YouTubeVideoProviderInterface
{
    public function getAllVideos(): array
    {
        $videos = [];

        foreach ($this->importVideosFromFile() as $video) {
            $videos[] = new YouTubeVideo(
                $video->video_id,
                $video->title,
                new DateTimeImmutable($video->published_at),
                $video->duration,
            );
        }

        return $videos;
    }

    private function importVideosFromFile(): object
    {
        return json_decode(file_get_contents(__DIR__ . '/../../../data/videos.json'));
    }
}
