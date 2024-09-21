<?php

namespace Claroline\YouTubeBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_youtube_video')]
#[ORM\Entity]
class Video extends AbstractResource
{
    #[ORM\Column(name: 'video_id', type: 'string', nullable: false)]
    private string $videoId;

    #[ORM\Column(name: 'timecode_start', type: 'integer', nullable: true)]
    private ?int $timecodeStart = null;

    #[ORM\Column(name: 'timecode_end', type: 'integer', nullable: true)]
    private ?int $timecodeEnd = null;

    #[ORM\Column(name: 'autoplay', type: 'boolean', nullable: false)]
    private bool $autoplay = false;

    #[ORM\Column(name: 'looping', type: 'boolean', nullable: false)]
    private bool $looping = false;

    #[ORM\Column(name: 'controls', type: 'boolean', nullable: false)]
    private bool $controls = true;

    #[ORM\Column(name: 'resume', type: 'boolean', nullable: false)]
    private bool $resume = false;

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

    public function getResume(): bool
    {
        return $this->resume;
    }

    public function setResume(bool $resume): void
    {
        $this->resume = $resume;
    }
}
