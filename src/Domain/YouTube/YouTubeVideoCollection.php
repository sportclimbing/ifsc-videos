<?php declare(strict_types=1);

/**
 * @license  http://opensource.org/licenses/mit-license.php MIT
 * @link     https://github.com/nicoSWD
 * @author   Nicolas Oelgart <nico@oelgart.com>
 */
namespace nicoSWD\IfscVideos\Domain\YouTube;

use Closure;

final readonly class YouTubeVideoCollection
{
    public function __construct(
        private YouTubeVideoProviderInterface $youTubeVideoProvider,
    ) {
    }

    /** @return YouTubeVideo[] */
    public function getVideosForSeason(int $season): array
    {
        return array_filter(
            $this->youTubeVideoProvider->getAllVideos(),
            $this->seasonFilter($season),
        );
    }

    public function getAllVideos(): array
    {
        return $this->youTubeVideoProvider->getAllVideos();
    }

    /** @return string[] */
    public function fetchRestrictedRegionsForVideo(string $videoId): array
    {
        return $this->youTubeVideoProvider->fetchRestrictedRegionsForVideo($videoId);
    }

    private function seasonFilter(int $season): Closure
    {
        return static fn (YouTubeVideo $youTubeVideo): bool => (int) $youTubeVideo->publishedAt->format('Y') === $season;
    }
}
