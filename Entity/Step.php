<?php

namespace Innova\PathBundle\Entity;

use Innova\PathBundle\Entity\Step2ResourceNode;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Step
 *
 * @ORM\Table("innova_step")
 * @ORM\Entity
 */
class Step
{
    /**
     * Unique identifier of the step
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Name of the step
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * Depth of the step in the Path
     * @var integer
     *
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * Order of the steps relative to his siblings in the path
     * @var integer
     *
     * @ORM\Column(name="stepOrder", type="integer")
     */
    protected $stepOrder;

    /**
     * Parent step
     * @var \Innova\PathBundle\Entity\Step
     * 
     * @ORM\ManyToOne(targetEntity="Step", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * Children steps
     * @var \Doctrine\Common\Collections\ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Step", mappedBy="parent")
     */
    protected $children;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="expanded", type="boolean")
     */
    protected $expanded;

    /**
     * @var string
     *
     * @ORM\Column(name="instructions", type="text", nullable=true)
     */
    protected $instructions = null;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    protected $image;

    /**
     * @var boolean
     *
     * @ORM\Column(name="withTutor", type="boolean")
     */
    protected $withTutor;

    /**
     * @var boolean
     *
     * @ORM\Column(name="withComputer", type="boolean")
     */
    protected $withComputer;

    /**
     * Step duration
     * @var \DateTime
     *
     * @ORM\Column(name="duration", type="datetime", nullable=true)
     */
    protected $duration;

    /**
     * Path
     * @var Innova\PathBundle\Entity\Path
     * 
     * @ORM\ManyToOne(targetEntity="Path", inversedBy="steps")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $path;

    /**
     * Type of the step
     * @var \Innova\PathBundle\Entity\StepType
     * 
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
     * @ORM\OneToMany(targetEntity="Step2ResourceNode", mappedBy="step")
     */
    protected $step2ResourceNodes;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->step2ResourceNodes = new ArrayCollection();
    }
    
    
    /**
     * Get id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set name
     * @param string $name
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }
    
    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set lvl
     * @param integer $lvl
     * @return \Innova\PathBundle\Entity\Step
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
     * Set expanded
     * @param  boolean $expanded
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setExpanded($expanded)
    {
        $this->expanded = $expanded;

        return $this;
    }

    /**
     * Get expanded
     * @return boolean
     */
    public function getExpanded()
    {
        return $this->expanded;
    }

    /**
     * Set instructions
     * @param  string $instructions
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;

        return $this;
    }

    /**
     * Get instructions
     * @return string
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * Set image
     * @param  string $image
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setImage($image)
    {
        $this->image= $image;
    
        return $this;
    }
    
    /**
     * Get image
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set withTutor
     * @param  boolean $withTutor
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setWithTutor($withTutor)
    {
        $this->withTutor = $withTutor;

        return $this;
    }

    /**
     * Get withTutor
     * @return boolean
     */
    public function isWithTutor()
    {
        return $this->withTutor;
    }

    /**
     * Set withComputer
     * @param  boolean $withComputer
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setWithComputer($withComputer)
    {
        $this->withComputer = $withComputer;

        return $this;
    }

    /**
     * Get withComputer
     * @return boolean
     */
    public function isWithComputer()
    {
        return $this->withComputer;
    }

    /**
     * Set duration
     * @param  \DateTime $duration
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     * @return \DateTime
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set stepOrder
     * @param  integer $stepOrder
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setStepOrder($stepOrder)
    {
        $this->stepOrder = $stepOrder;

        return $this;
    }

    /**
     * Get oreder of the step
     * @return integer
     */
    public function getStepOrder()
    {
        return $this->stepOrder;
    }

    /**
     * Set type of the step
     * @param  \Innova\PathBundle\Entity\StepType $stepType
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setStepType(\Innova\PathBundle\Entity\StepType $stepType = null)
    {
        $this->stepType = $stepType;

        return $this;
    }

    /**
     * Get stepType
     * @return \Innova\PathBundle\Entity\StepType
     */
    public function getStepType()
    {
        return $this->stepType;
    }

    /**
     * Set stepWho
     * @param  \Innova\PathBundle\Entity\StepWho $stepWho
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setStepWho(\Innova\PathBundle\Entity\StepWho $stepWho = null)
    {
        $this->stepWho = $stepWho;

        return $this;
    }

    /**
     * Get stepWho
     * @return \Innova\PathBundle\Entity\StepWho
     */
    public function getStepWho()
    {
        return $this->stepWho;
    }

    /**
     * Set stepWhere
     * @param  \Innova\PathBundle\Entity\StepWhere $stepWhere
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setStepWhere(\Innova\PathBundle\Entity\StepWhere $stepWhere = null)
    {
        $this->stepWhere = $stepWhere;

        return $this;
    }

    /**
     * Get stepWhere
     * @return \Innova\PathBundle\Entity\StepWhere
     */
    public function getStepWhere()
    {
        return $this->stepWhere;
    }

    /**
     * Set path
     * @param  \Innova\PathBundle\Entity\Path $path
     * @return \Innova\PathBundle\Entity\
     */
    public function setPath(\Innova\PathBundle\Entity\Path $path = null)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     * @return \Innova\PathBundle\Entity\Path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set parent
     * @param  \Innova\PathBundle\Entity\Step $parent
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setParent(Step $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     * @return \Innova\PathBundle\Entity\Step
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
    
    /**
     * Add new child to the step
     * @param \Innova\PathBundle\Entity\Step $step
     * @return \Innova\PathBundle\Entity\Step
     */
    public function addChild(Step $step)
    {
        $this->children->add($step);
        $step->setParent($this);
        
        return $this;
    }
    
    /**
     * Remove a step from children
     * @param \Innova\PathBundle\Entity\Step $step
     * @return \Innova\PathBundle\Entity\Step
     */
    public function removeChild(Step $step) 
    {
        $this->children->removeElement($step);
        $step->setParent(null);
        
        return $this;
    }
    
    /**
     * Add step2ResourceNodes
     * @param \Innova\PathBundle\Entity\Step2ResourceNode $step2ResourceNodes
     * @return \Innova\PathBundle\Entity\Step
     */
    public function addStep2ResourceNode(Step2ResourceNode $step2ResourceNodes)
    {
        $this->step2ResourceNodes[] = $step2ResourceNodes;

        return $this;
    }

    /**
     * Remove step2ResourceNodes
     * @param \Innova\PathBundle\Entity\Step2ResourceNode $step2ResourceNodes
     */
    public function removeStep2ResourceNode(Step2ResourceNode $step2ResourceNodes)
    {
        $this->step2ResourceNodes->removeElement($step2ResourceNodes);
        
        return $this;
    }

    /**
     * Get step2ResourceNodes
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStep2ResourceNodes()
    {
        return $this->step2ResourceNodes;
    }
    
    /**
     * Get all siblings of the steps
     * @throws \Exception
     * @return array
     */
    public function getSiblings()
    {
        $siblings = array ();
        
        $parent = $this->getParent();
        if (!empty($parent)) {
            // Current step has a parent
            $siblings = clone $parent->getChildren();
        
            // Remove current step from parent children
            $siblings->removeElement($this);
            $siblings = $siblings->toArray();
            
            // Order siblings by stepOrder
            $sortSiblings = function ($a, $b) {
                if ($a->getStepOrder() === $b->getStepOrder()) {
                    return 0;
                }
                return ($a->getStepOrder() < $b->getStepOrder()) ? -1 : 1;
            };
            
            uasort($siblings, $sortSiblings);
        }
        
        return $siblings;
    }
    
    /**
     * Get all the parents chain of the step
     * @return array
     */
    public function getParents()
    {
        $parents = array ();
        
        $parent = $this->getParent();
        if (!empty($parent)) {
            $parents[] = $parent;
            $parents = array_merge($parents, $parent->getParents());
            
            // Sort parents
            $sortParents = function ($a, $b) {
                if ($a->getLvl() === $b->getLvl()) {
                    return 0;
                }
                return ($a->getLvl() < $b->getLvl()) ? -1 : 1;
            };
            
            uasort($parents, $sortParents);
        }
        
        return $parents;
    }
}
