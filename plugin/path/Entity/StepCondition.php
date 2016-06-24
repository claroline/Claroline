<?php

namespace Innova\PathBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * StepCondition.
 *
 * @ORM\Table(name="innova_stepcondition")
 * @ORM\Entity
 */
class StepCondition implements \JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * criteriagroups linked to the condition.
     *
     * @ORM\OneToMany(targetEntity="Innova\PathBundle\Entity\Criteriagroup", mappedBy="condition", indexBy="id", cascade={"persist", "remove"})
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private $criteriagroups;

    /**
     * Step the condition belongs to.
     *
     * @var \Innova\PathBundle\Entity\Step
     *
     * @ORM\OneToOne(targetEntity="Innova\PathBundle\Entity\Step", inversedBy="condition")
     */
    protected $step;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->criteriagroups = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add criteriaGroup.
     *
     * @param \Innova\PathBundle\Entity\Criteriagroup $criteriaGroup
     *
     * @return \Innova\PathBundle\Entity\StepCondition
     */
    public function addCriteriagroup(Criteriagroup $criteriaGroup)
    {
        if (!$this->criteriagroups->contains($criteriaGroup)) {
            $this->criteriagroups->add($criteriaGroup);
        }

        $criteriaGroup->setCondition($this);

        return $this;
    }

    /**
     * Remove CriteriaGroup.
     *
     * @param \Innova\PathBundle\Entity\Criteriagroup $criteriaGroup
     *
     * @return \Innova\PathBundle\Entity\StepCondition
     */
    public function removeCriteriagroup(Criteriagroup $criteriaGroup)
    {
        if ($this->criteriagroups->contains($criteriaGroup)) {
            $this->criteriagroups->removeElement($criteriaGroup);
        }

        $criteriaGroup->setCondition(null);

        return $this;
    }

    /**
     * Get criteriagroups.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCriteriagroups()
    {
        return $this->criteriagroups;
    }

    /**
     * Get root criteriagroup of the condition.
     *
     * @throws \Exception
     *
     * @return \Innova\PathBundle\Entity\Criteriagroup
     */
    public function getRootCriteriagroup()
    {
        $root = null;

        if (!empty($this->criteriagroups)) {
            foreach ($this->criteriagroups as $criteriaGroup) {
                if (null === $criteriaGroup->getParent()) {
                    // Root criteriagroup found
                    $root = $criteriaGroup;

                    break;
                }
            }
        }

        return $root;
    }

    /**
     * Set step.
     *
     * @param \Innova\PathBundle\Entity\Step $step
     *
     * @return StepCondition
     */
    public function setStep(\Innova\PathBundle\Entity\Step $step = null)
    {
        if ($step !== $this->step) {
            $this->step = $step;
            if (null !== $step) {
                $step->setCondition($this);
            }
        }

        return $this;
    }

    /**
     * Get step.
     *
     * @return \Innova\PathBundle\Entity\Step
     */
    public function getStep()
    {
        return $this->step;
    }

    public function jsonSerialize()
    {
        // Initialize data array
        $jsonArray = [
            'id' => $this->id,
            'scid' => $this->id,
        ];

        $criteriagroups = [];
        $rootCriteriagroup = $this->getRootCriteriagroup();
        if (!empty($rootCriteriagroup)) {
            $criteriagroups[] = $rootCriteriagroup;
        }

        $jsonArray['criteriagroups'] = $criteriagroups;

        return $jsonArray;
    }
}
