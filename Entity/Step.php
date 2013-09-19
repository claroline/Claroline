<?php

namespace Innova\PathBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * Step
 *
 * @ORM\Table("innova_step")
 * @ORM\Entity
 */
class Step extends AbstractResource
{
   
    /**
     * @var string
     *
     * @ORM\Column(name="uuid", type="string", length=255)
     */
    private $uuid;

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer")
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="parent", type="string", length=255)
     */
    private $parent;

    /**
     * @var boolean
     *
     * @ORM\Column(name="expanded", type="boolean")
     */
    private $expanded;

    /**
     * @var string
     *
     * @ORM\Column(name="instructions", type="text")
     */
    private $instructions;

    /**
     * @var boolean
     *
     * @ORM\Column(name="withTutor", type="boolean")
     */
    private $withTutor;

    /**
     * @var boolean
     *
     * @ORM\Column(name="withComputer", type="boolean")
     */
    private $withComputer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="duration", type="datetime")
     */
    private $duration;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deployable", type="boolean")
     */
    private $deployable;

    /**
    * @ORM\ManyToOne(targetEntity="StepType", inversedBy="steps")
    */
    protected $stepType;

    /**
    * @ORM\ManyToOne(targetEntity="StepWho", inversedBy="steps")
    */
    protected $stepWho;

    /**
    * @ORM\ManyToOne(targetEntity="StepWhere", inversedBy="steps")
    */
    protected $stepWhere;

    /**
     * Set uuid
     *
     * @param string $uuid
     * @return Step
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string 
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set expanded
     *
     * @param boolean $expanded
     * @return Step
     */
    public function setExpanded($expanded)
    {
        $this->expanded = $expanded;

        return $this;
    }

    /**
     * Get expanded
     *
     * @return boolean 
     */
    public function getExpanded()
    {
        return $this->expanded;
    }

    /**
     * Set instructions
     *
     * @param string $instructions
     * @return Step
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;

        return $this;
    }

    /**
     * Get instructions
     *
     * @return string 
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * Set withTutor
     *
     * @param boolean $withTutor
     * @return Step
     */
    public function setWithTutor($withTutor)
    {
        $this->withTutor = $withTutor;

        return $this;
    }

    /**
     * Get withTutor
     *
     * @return boolean 
     */
    public function getWithTutor()
    {
        return $this->withTutor;
    }

    /**
     * Set withComputer
     *
     * @param boolean $withComputer
     * @return Step
     */
    public function setWithComputer($withComputer)
    {
        $this->withComputer = $withComputer;

        return $this;
    }

    /**
     * Get withComputer
     *
     * @return boolean 
     */
    public function getWithComputer()
    {
        return $this->withComputer;
    }

    /**
     * Set duration
     *
     * @param \DateTime $duration
     * @return Step
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return \DateTime 
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set deployable
     *
     * @param boolean $deployable
     * @return Step
     */
    public function setDeployable($deployable)
    {
        $this->deployable = $deployable;

        return $this;
    }

    /**
     * Get deployable
     *
     * @return boolean 
     */
    public function getDeployable()
    {
        return $this->deployable;
    }

    /**
     * Set order
     *
     * @param integer $order
     * @return Step
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer 
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set parent
     *
     * @param string $parent
     * @return Step
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return string 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set resourceNode
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $resourceNode
     * @return Step
     */
    public function setResourceNode(\Claroline\CoreBundle\Entity\Resource\ResourceNode $resourceNode = null)
    {
        $this->resourceNode = $resourceNode;

        return $this;
    }

    /**
     * Get resourceNode
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode 
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }


    /**
     * Set stepType
     *
     * @param \Innova\PathBundle\Entity\StepType $stepType
     * @return Step
     */
    public function setStepType(\Innova\PathBundle\Entity\StepType $stepType = null)
    {
        $this->stepType = $stepType;

        return $this;
    }

    /**
     * Get stepType
     *
     * @return \Innova\PathBundle\Entity\StepType 
     */
    public function getStepType()
    {
        return $this->stepType;
    }

    /**
     * Set stepWho
     *
     * @param \Innova\PathBundle\Entity\StepWho $stepWho
     * @return Step
     */
    public function setStepWho(\Innova\PathBundle\Entity\StepWho $stepWho = null)
    {
        $this->stepWho = $stepWho;

        return $this;
    }

    /**
     * Get stepWho
     *
     * @return \Innova\PathBundle\Entity\StepWho 
     */
    public function getStepWho()
    {
        return $this->stepWho;
    }

    /**
     * Set stepWhere
     *
     * @param \Innova\PathBundle\Entity\StepWhere $stepWhere
     * @return Step
     */
    public function setStepWhere(\Innova\PathBundle\Entity\StepWhere $stepWhere = null)
    {
        $this->stepWhere = $stepWhere;

        return $this;
    }

    /**
     * Get stepWhere
     *
     * @return \Innova\PathBundle\Entity\StepWhere 
     */
    public function getStepWhere()
    {
        return $this->stepWhere;
    }

}
