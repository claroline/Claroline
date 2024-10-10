<?php

namespace Claroline\AudioPlayerBundle\Entity\Quiz\ItemType;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Claroline\AudioPlayerBundle\Entity\Quiz\Misc\Section;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Model\PenaltyTrait;

/**
 * A Waveform question.
 */
#[ORM\Table(name: 'claro_audio_interaction_waveform')]
#[ORM\Entity]
class WaveformQuestion extends AbstractItem
{
    /*
     * The penalty to apply to each wrong association
     */
    use PenaltyTrait;

    #[ORM\Column(name: 'url', type: Types::STRING)]
    private ?string $url = null;

    #[ORM\Column(name: 'tolerance', type: Types::FLOAT)]
    private float $tolerance = 1;

    #[ORM\Column(name: 'answers_limit', type: Types::INTEGER)]
    private int $answersLimit = 0;

    /**
     * @var Collection<int, Section>
     */
    #[ORM\OneToMany(targetEntity: Section::class, mappedBy: 'waveform', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $sections;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getTolerance(): ?float
    {
        return $this->tolerance;
    }

    public function setTolerance(float $tolerance): void
    {
        $this->tolerance = $tolerance;
    }

    public function getAnswersLimit(): int
    {
        return $this->answersLimit;
    }

    public function setAnswersLimit(int $answersLimit): void
    {
        $this->answersLimit = $answersLimit;
    }

    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): void
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
        }
    }

    public function removeSection(Section $section): void
    {
        if ($this->sections->contains($section)) {
            $this->sections->removeElement($section);
        }
    }

    public function emptySections(): void
    {
        $this->sections->clear();
    }
}
