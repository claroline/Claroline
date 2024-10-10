<?php

namespace Claroline\AudioPlayerBundle\Entity\Quiz\Misc;

use Claroline\AppBundle\Entity\Display\Color;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AudioPlayerBundle\Entity\Quiz\ItemType\WaveformQuestion;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

#[ORM\Table(name: 'claro_audio_section')]
#[ORM\Entity]
class Section implements AnswerPartInterface
{
    use Id;
    use Uuid;
    use Color;
    use FeedbackTrait;
    use ScoreTrait;

    #[ORM\Column(name: 'section_start', type: Types::FLOAT, nullable: false)]
    private ?float $start = null;

    #[ORM\Column(name: 'section_end', type: Types::FLOAT, nullable: false)]
    private ?float $end = null;

    #[ORM\Column(name: 'start_tolerance', type: Types::FLOAT, nullable: false)]
    private float $startTolerance = 0;

    #[ORM\Column(name: 'end_tolerance', type: Types::FLOAT, nullable: false)]
    private float $endTolerance = 0;

    #[ORM\JoinColumn(name: 'waveform_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: WaveformQuestion::class, cascade: ['persist'], inversedBy: 'sections')]
    private ?WaveformQuestion $waveform = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getStart(): ?float
    {
        return $this->start;
    }

    public function setStart(float $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): ?float
    {
        return $this->end;
    }

    public function setEnd(float $end): void
    {
        $this->end = $end;
    }

    /**
     * @return float
     */
    public function getStartTolerance(): float
    {
        return $this->startTolerance;
    }

    public function setStartTolerance(float $startTolerance): void
    {
        $this->startTolerance = $startTolerance;
    }

    public function getEndTolerance(): float
    {
        return $this->endTolerance;
    }

    public function setEndTolerance(float $endTolerance): void
    {
        $this->endTolerance = $endTolerance;
    }

    public function getWaveform(): ?WaveformQuestion
    {
        return $this->waveform;
    }

    public function setWaveform(WaveformQuestion $waveform): void
    {
        $this->waveform = $waveform;
    }
}
