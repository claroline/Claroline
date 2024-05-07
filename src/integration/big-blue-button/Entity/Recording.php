<?php

namespace Claroline\BigBlueButtonBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_bigbluebuttonbundle_recording")
 */
class Recording
{
    use Id;
    use Uuid;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\BigBlueButtonBundle\Entity\BBB", inversedBy="recordings")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?BBB $meeting = null;

    /**
     * @ORM\Column()
     */
    private ?string $recordId = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $startTime = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $endTime;

    /**
     * @ORM\Column()
     */
    private ?string $status = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $participants = 0;

    /**
     * @ORM\Column(type="json")
     */
    private ?array $medias = [];

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getMeeting(): BBB
    {
        return $this->meeting;
    }

    public function setMeeting(BBB $meeting): void
    {
        $this->meeting = $meeting;
    }

    public function getRecordId(): string
    {
        return $this->recordId;
    }

    public function setRecordId(string $recordId): void
    {
        $this->recordId = $recordId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function setStartTime(string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function setEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getParticipants(): int
    {
        return $this->participants;
    }

    public function setParticipants(int $participants): void
    {
        $this->participants = $participants;
    }

    public function getMedias(): array
    {
        return $this->medias;
    }

    public function setMedias(array $medias): void
    {
        $this->medias = $medias;
    }
}
