<?php

namespace Innova\PathBundle\Entity\Path;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Innova\PathBundle\Entity\Step;

/**
 * Path.
 *
 * @ORM\Table(name="innova_path")
 * @ORM\Entity()
 */
class Path extends AbstractResource
{
    /**
     * Steps linked to the path.
     *
     * @var ArrayCollection|Step[]
     *
     * @ORM\OneToMany(targetEntity="Innova\PathBundle\Entity\Step", mappedBy="path", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({
     *     "order" = "ASC"
     * })
     */
    protected $steps;

    /**
     * @var bool
     *
     * @ORM\Column(name="modified", type="boolean")
     */
    protected $modified = false;

    /**
     * Numbering of the steps.
     *
     * @var string
     *
     * @ORM\Column
     */
    protected $numbering = 'none';

    /**
     * Description of the path.
     *
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * Is it possible for the user to manually set the progression.
     *
     * @var bool
     *
     * @ORM\Column(name="manual_progression_allowed", type="boolean")
     */
    protected $manualProgressionAllowed = true;

    /**
     * Show overview to users or directly start the path.
     *
     * @ORM\Column(name="show_overview", type="boolean", options={"default" = 1})
     *
     * @var bool
     */
    private $showOverview = true;

    /**
     * Show an end page when the user has finished the path.
     *
     * @ORM\Column(name="show_end_page", type="boolean")
     *
     * @var bool
     */
    private $showEndPage = false;

    /**
     * A message to display at the end of the path.
     *
     * @ORM\Column(name="end_message", type="text", nullable=true)
     *
     * @var string
     */
    private $endMessage = '';

    /**
     * Force the opening of secondary resources.
     *
     * @ORM\Column(options={"default" : "_self"})
     *
     * @var string
     */
    private $secondaryResourcesTarget = '_self';

    /**
     * @ORM\Column(name="score_total", type="float", options={"default" = 100})
     *
     * @var float
     */
    private $scoreTotal = 100;

    /**
     * Score to obtain to pass.
     *
     * @ORM\Column(name="success_score", type="float", nullable=true)
     *
     * @var float
     */
    private $successScore = 50;

    /**
     * @ORM\Column(name="show_score", type="boolean")
     *
     * @var bool
     */
    private $showScore = false;

    public function __construct()
    {
        parent::__construct();

        $this->steps = new ArrayCollection();
    }

    /**
     * Add step.
     *
     * @return Path
     */
    public function addStep(Step $step)
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
        }

        return $this;
    }

    /**
     * Remove step.
     *
     * @return Path
     */
    public function removeStep(Step $step)
    {
        if ($this->steps->contains($step)) {
            $this->steps->removeElement($step);
        }

        return $this;
    }

    public function getStep($stepId)
    {
        $found = null;

        foreach ($this->steps as $step) {
            if ($step->getUuid() === $stepId) {
                $found = $step;
                break;
            }
        }

        return $found;
    }

    /**
     * Remove all steps.
     *
     * @return Path
     *
     * @deprecated
     */
    public function emptySteps()
    {
        $this->steps->clear();

        return $this;
    }

    /**
     * Get steps.
     *
     * @return ArrayCollection|Step[]
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Get numbering.
     *
     * @return string
     */
    public function getNumbering()
    {
        return $this->numbering;
    }

    /**
     * Set numbering.
     *
     * @param string $numbering
     *
     * @return Path
     */
    public function setNumbering($numbering)
    {
        $this->numbering = $numbering;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Path
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get manualProgressionAllowed.
     *
     * @return bool
     */
    public function isManualProgressionAllowed()
    {
        return $this->manualProgressionAllowed;
    }

    /**
     * Set manualProgressionAllowed.
     *
     * @param bool $manualProgressionAllowed
     *
     * @return Path
     */
    public function setManualProgressionAllowed($manualProgressionAllowed)
    {
        $this->manualProgressionAllowed = $manualProgressionAllowed;

        return $this;
    }

    /**
     * Gets all the path step in a flat array in correct order.
     *
     * @return Step[]
     */
    public function getOrderedSteps()
    {
        $flatten = [];

        $roots = $this->getRootSteps();
        foreach ($roots as $root) {
            $flatten = array_merge($flatten, $this->getFlatSteps($root));
        }

        return $flatten;
    }

    private function getFlatSteps(Step $step)
    {
        $steps = [$step];
        foreach ($step->getChildren() as $child) {
            $steps = array_merge($steps, $this->getFlatSteps($child));
        }

        return $steps;
    }

    /**
     * Get root step of the path.
     *
     * @return Step[]
     */
    public function getRootSteps()
    {
        $roots = [];

        if (!empty($this->steps)) {
            foreach ($this->steps as $step) {
                if (null === $step->getParent()) {
                    // Root step found
                    $roots[] = $step;
                }
            }
        }

        return $roots;
    }

    /**
     * Set show overview.
     *
     * @param bool $showOverview
     */
    public function setShowOverview($showOverview)
    {
        $this->showOverview = $showOverview;
    }

    /**
     * Is overview shown ?
     *
     * @return bool
     */
    public function getShowOverview()
    {
        return $this->showOverview;
    }

    /**
     * Get the opening target for secondary resources.
     *
     * @return string
     */
    public function getSecondaryResourcesTarget()
    {
        return $this->secondaryResourcesTarget;
    }

    /**
     * Set the opening target for secondary resources.
     *
     * @param $secondaryResourcesTarget
     */
    public function setSecondaryResourcesTarget($secondaryResourcesTarget)
    {
        $this->secondaryResourcesTarget = $secondaryResourcesTarget;
    }

    public function hasResources()
    {
        foreach ($this->steps as $step) {
            if ($step->hasResources()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return float
     */
    public function getScoreTotal()
    {
        return $this->scoreTotal;
    }

    /**
     * @param float $scoreTotal
     */
    public function setScoreTotal($scoreTotal)
    {
        $this->scoreTotal = $scoreTotal;
    }

    /**
     * @return float
     */
    public function getSuccessScore()
    {
        return $this->successScore;
    }

    /**
     * @param float $successScore
     */
    public function setSuccessScore($successScore)
    {
        $this->successScore = $successScore;
    }

    /**
     * @return bool
     */
    public function getShowScore()
    {
        return $this->showScore;
    }

    /**
     * @param bool $showScore
     */
    public function setShowScore($showScore)
    {
        $this->showScore = $showScore;
    }

    /**
     * Set show end page.
     *
     * @param bool $showEndPage
     */
    public function setShowEndPage($showEndPage)
    {
        $this->showEndPage = $showEndPage;
    }

    /**
     * Is end page shown ?
     *
     * @return bool
     */
    public function getShowEndPage()
    {
        return $this->showEndPage;
    }

    public function getEndMessage()
    {
        return $this->endMessage;
    }

    public function setEndMessage($endMessage)
    {
        $this->endMessage = $endMessage;
    }
}
