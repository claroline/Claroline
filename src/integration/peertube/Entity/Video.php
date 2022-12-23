<?php

namespace Claroline\PeerTubeBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_peertube_video")
 */
class Video extends AbstractResource
{
    /**
     * The URL (scheme + host) of the PeerTube server where the video is stored.
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $server;

    /**
     * The UUID of the video (retrieved from the PeerTube API and required to construct the embedded URL).
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $originalUuid;

    /**
     * The short UUID of the video (retrieved from the the PeerTube share URL).
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $shortUuid;

    public function getServer(): ?string
    {
        return $this->server;
    }

    public function setServer(string $server): void
    {
        $this->server = $server;
    }

    public function getOriginalUuid(): ?string
    {
        return $this->originalUuid;
    }

    public function setOriginalUuid(string $originalUuid): void
    {
        $this->originalUuid = $originalUuid;
    }

    public function getShortUuid(): ?string
    {
        return $this->shortUuid;
    }

    public function setShortUuid(string $shortUuid): void
    {
        $this->shortUuid = $shortUuid;
    }

    public function getUrl(): ?string
    {
        return $this->server.'/w/'.$this->shortUuid;
    }

    public function getEmbeddedUrl(): ?string
    {
        return $this->server.'/videos/embed/'.$this->originalUuid;
    }
}
