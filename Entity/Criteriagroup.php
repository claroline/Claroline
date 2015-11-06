<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Innova\PathBundle\Entity\Criterion;
use Innova\PathBundle\Entity\StepCondition;
/**
 * Criteriagroup
 *
 * @ORM\Table(name="innova_stepcondition_criteriagroup")
 * @ORM\Entity(repositoryClass="Innova\PathBundle\Repository\CriteriagroupRepository")
 */
class Criteriagroup implements \JsonSerializable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Depth of the criteriagroup in the Condition
     * @var integer
     *
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * Parent criteriagroup
     * @var \Innova\PathBundle\Entity\Criteriagroup
     *
     * @ORM\ManyToOne(targetEntity="Criteriagroup", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * Children criteriagroup
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Criteriagroup", mappedBy="parent", indexBy="id", cascade={"persist", "remove"})
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $children;

    /**
     * StepCondition
     * @var \Innova\PathBundle\Entity\StepCondition
     *
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\StepCondition", inversedBy="criteriagroups")
     * @ORM\JoinColumn(name="stepcondition_id", referencedColumnName="id")
     */
    protected $stepcondition;

    /**
     * Criteria linked to the criteriagroup
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Innova\PathBundle\Entity\Criterion", mappedBy="criteriagroup", indexBy="id", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $criteria;

    /**
     * Order of the criteriagroups relative to its siblings in the step
     * @var integer
     *
     * @ORM\Column(name="criteriagroup_order", type="integer")
     */
    protected $order;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->children     = new ArrayCollection();
        $this->criteria     = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set lvl
     * @param integer $lvl
     * @return \Innova\PathBundle\Entity\Criteriagroup
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }
    /**
     * Set parent
     * @param  \Innova\PathBundle\Entity\Criteriagroup $parent
     * @return \Innova\PathBundle\Entity\Criteriagroup
     */
    public function setParent(Criteriagroup $parent = null)
    {
        if ($parent != $this->parent) {
            $this->parent = $parent;
            $parent->addChild($this);
        }

        return $this;
    }

    /**
     * Get parent
     * @return \Innova\PathBundle\Entity\Criteriagroup
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get children of the step
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function hasChildren()
    {
        return !empty($this->children) && 0 < $this->children->count();
    }

    /**
     * Add new child to the criteriagroup
     * @param \Innova\PathBundle\Entity\Criteriagroup $criteriagroup
     * @return \Innova\PathBundle\Entity\Criteriagroup
     */
    public function addChild(Criteriagroup $criteriagroup)
    {
        if (!$this->children->contains($criteriagroup)) {
            $this->children->add($criteriagroup);
            $criteriagroup->setParent($this);
        }

        return $this;
    }

    /**
     * Remove a step from children
     * @param \Innova\PathBundle\Entity\Criteriagroup $criteriagroup
     * @return \Innova\PathBundle\Entity\Criteriagroup
     */
    public function removeChild(Criteriagroup $criteriagroup)
    {
        if ($this->children->contains($criteriagroup)) {
            $this->children->removeElement($criteriagroup);
            $criteriagroup->setParent(null);
        }

        return $this;
    }

    /**
     * Set stepcondition
     * @param  \Innova\PathBundle\Entity\StepCondition $stepcondition
     * @return \Innova\PathBundle\Entity\Criteriagroup
     */
    public function setStepCondition(Stepcondition $stepcondition = null)
    {
        if ($stepcondition !== $this->stepcondition) {
            $this->stepcondition = $stepcondition;
            if (null !== $stepcondition) {
                $stepcondition->addCriteriagroup($this);
            }
        }

        return $this;
    }

    /**
     * Get stepcondition
     * @return \Innova\PathBundle\Entity\StepCondition
     */
    public function getStepCondition()
    {
        return $this->stepcondition;
    }

    /**
     * Add criterion
     * @param  \Innova\PathBundle\Entity\Criterion $criterion
     * @return \Innova\PathBundle\Entity\Criteriagroup
     */
    public function addCriterion(Criterion $criterion)
    {
        if (!$this->criteria->contains($criterion)) {
            $this->criteria->add($criterion);
        }

        $criterion->setCriteriagroup($this);

        return $this;
    }

    /**
     * Remove criterion
     * @param \Innova\PathBundle\Entity\Criterion $criterion
     * @return \Innova\PathBundle\Entity\Criteriagroup
     */
    public function removeCriterion(Criterion $criterion)
    {
        if ($this->criteria->contains($criterion)) {
            $this->criteria->removeElement($criterion);
        }

        $criterion->setCriteriagroup(null);

        return $this;
    }

    /**
     * Get criteria
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Set order
     * @param  integer $order
     * @return \Innova\PathBundle\Entity\Criteriagroup
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order of the step
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    public function jsonSerialize()
    {
        // Initialize data array
        $jsonArray = array (
            'id'                => $this->id,               // A local ID for the criteriagroup in the condition
            'cgid'              => $this->id,               // The real ID of the criteriagroup into the DB
            'lvl'               => $this->lvl,              // The depth of the criteriagroup in the condition structure
        );

        // Get step criteriagroup
        if (!empty($this->children)) {
            $jsonArray['criteriagroup'] = array_values($this->children->toArray());
        }

        // list of criteria attached to a criteriagroup
        if (!empty($this->criteria)) {
            $jsonArray['criterion'] = array_values($this->criteria->toArray());
        }

        return $jsonArray;
    }
}
