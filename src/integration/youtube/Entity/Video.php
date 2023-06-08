<?php

namespace Claroline\YouTubeBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_youtube_video")
 */
class Video extends AbstractResource
{
    /**
     * @ORM\Column(name="video_id", type="string", nullable=false)
     */
    private string $videoId;

    public function getVideoId(): string
    {
        return $this->videoId;
    }

    public function setVideoId(string $videoId): void
    {
        $this->videoId = $videoId;
    }

    public function getUrl(): string
    {
        return 'https://www.youtube.com/watch?v='.$this->videoId;
    }
}
