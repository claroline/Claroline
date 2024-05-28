<?php

namespace Claroline\PeerTubeBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="claro_peertube_video")
 */
class Video extends AbstractResource
{
    /**
     * The URL (scheme + host) of the PeerTube server where the video is stored.
     *
     * @ORM\Column(type="string")
     */
    private ?string $server = null;

    /**
     * The UUID of the video (retrieved from the PeerTube API and required to construct the embedded URL).
     *
     * @ORM\Column(type="string")
     */
    private ?string $originalUuid = null;

    /**
     * The short UUID of the video (retrieved from the PeerTube share URL).
     *
     * @ORM\Column(type="string")
     */
    private ?string $shortUuid = null;

    /**
     * @ORM\Column(name="timecode_start", type="integer", nullable=true)
     */
    private ?int $timecodeStart = null;

    /**
     * @ORM\Column(name="timecode_end", type="integer", nullable=true)
     */
    private ?int $timecodeEnd = null;

    /**
     * @ORM\Column(name="autoplay", type="boolean", nullable=false)
     */
    private bool $autoplay = false;

    /**
     * @ORM\Column(name="looping", type="boolean", nullable=false)
     */
    private bool $looping = false;

    /**
     * @ORM\Column(name="controls", type="boolean", nullable=false)
     */
    private bool $controls = true;

    /**
     * @ORM\Column(name="peertubeLink", type="boolean", nullable=false)
     */
    private bool $peertubeLink = false;

    /**
     * @ORM\Column(name="resume", type="boolean", nullable=false)
     */
    private bool $resume = false;

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

    public function getTimecodeStart(): ?int
    {
        return $this->timecodeStart;
    }

    public function setTimecodeStart(?int $timecodeStart): void
    {
        $this->timecodeStart = $timecodeStart;
    }

    public function getTimecodeEnd(): ?int
    {
        return $this->timecodeEnd;
    }

    public function setTimecodeEnd(?int $timecodeEnd): void
    {
        $this->timecodeEnd = $timecodeEnd;
    }

    public function getAutoplay(): bool
    {
        return $this->autoplay;
    }

    public function setAutoplay(bool $autoplay): void
    {
        $this->autoplay = $autoplay;
    }

    public function getLooping(): bool
    {
        return $this->looping;
    }

    public function setLooping(bool $looping): void
    {
        $this->looping = $looping;
    }

    public function getControls(): bool
    {
        return $this->controls;
    }

    public function setControls(bool $controls): void
    {
        $this->controls = $controls;
    }

    public function getPeertubeLink(): bool
    {
        return $this->peertubeLink;
    }

    public function setPeertubeLink(bool $peertubeLink): void
    {
        $this->peertubeLink = $peertubeLink;
    }

    public function getResume(): bool
    {
        return $this->resume;
    }

    public function setResume(bool $resume): void
    {
        $this->resume = $resume;
    }
}
