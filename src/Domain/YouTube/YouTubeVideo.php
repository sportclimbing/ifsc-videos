<?php declare(strict_types=1);

/**
 * @license  http://opensource.org/licenses/mit-license.php MIT
 * @link     https://github.com/nicoSWD
 * @author   Nicolas Oelgart <nico@oelgart.com>
 */
namespace nicoSWD\IfscVideos\Domain\YouTube;

use DateTimeImmutable;

final readonly class YouTubeVideo
{
    public function __construct(
        public string $videoId,
        public string $title,
        public DateTimeImmutable $publishedAt,
        public int $duration,
        public array $restrictedRegions,
    ) {
    }
}
