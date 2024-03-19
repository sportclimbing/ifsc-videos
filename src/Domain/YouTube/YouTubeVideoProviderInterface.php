<?php

/**
 * @license  http://opensource.org/licenses/mit-license.php MIT
 * @link     https://github.com/nicoSWD
 * @author   Nicolas Oelgart <nico@oelgart.com>
 */
namespace nicoSWD\IfscVideos\Domain\YouTube;

interface YouTubeVideoProviderInterface
{
    /** @return YouTubeVideo[] */
    public function getAllVideos(): array;

    /** @return string[] */
    public function fetchRestrictedRegionsForVideo(string $videoId): array;
}
