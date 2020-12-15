<?php

namespace Claroline\AudioPlayerBundle\Entity\Quiz\ItemType;

use Claroline\AudioPlayerBundle\Entity\Quiz\Misc\Section;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Model\PenaltyTrait;

/**
 * A Waveform question.
 *
 * @ORM\Entity
 * @ORM\Table(name="claro_audio_interaction_waveform")
 */
class WaveformQuestion extends AbstractItem
{
    /*
     * The penalty to apply to each wrong association
     */
    use PenaltyTrait;

    /**
     * @ORM\Column(name="url", type="string")
     */
    private $url;

    /**
     * @ORM\Column(name="tolerance", type="float")
     */
    private $tolerance = 1;

    /**
     * @ORM\Column(name="answers_limit", type="integer")
     */
    private $answersLimit = 0;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\AudioPlayerBundle\Entity\Quiz\Misc\Section",
     *     mappedBy="waveform",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    protected $sections;

    /**
     * WaveformQuestion constructor.
     */
    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getTolerance()
    {
        return $this->tolerance;
    }

    public function setTolerance($tolerance)
    {
        $this->tolerance = $tolerance;
    }

    public function getAnswersLimit()
    {
        return $this->answersLimit;
    }

    public function setAnswersLimit($answersLimit)
    {
        $this->answersLimit = $answersLimit;
    }

    public function getSections()
    {
        return $this->sections;
    }

    public function addSection(Section $section)
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
        }

        return $this;
    }

    public function removeSection(Section $section)
    {
        if ($this->sections->contains($section)) {
            $this->sections->removeElement($section);
        }

        return $this;
    }

    public function emptySections()
    {
        $this->sections->clear();
    }
}
