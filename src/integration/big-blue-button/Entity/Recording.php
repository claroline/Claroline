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
     *
     * @var BBB
     */
    private $meeting;

    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $recordId;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $startTime;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $endTime;

    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $participants = 0;

    /**
     * @ORM\Column(type="json")
     *
     * @var array
     */
    private $medias = [];

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getMeeting(): BBB
    {
        return $this->meeting;
    }

    public function setMeeting(BBB $meeting)
    {
        $this->meeting = $meeting;
    }

    public function getRecordId(): string
    {
        return $this->recordId;
    }

    public function setRecordId(string $recordId)
    {
        $this->recordId = $recordId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function setStartTime(string $startTime)
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function setEndTime(string $endTime)
    {
        $this->endTime = $endTime;
    }

    public function getParticipants(): int
    {
        return $this->participants;
    }

    public function setParticipants(int $participants)
    {
        $this->participants = $participants;
    }

    public function getMedias(): array
    {
        return $this->medias;
    }

    public function setMedias(array $medias)
    {
        $this->medias = $medias;
    }
}
