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
    const DEFAULT_NAME = 'Step';
    
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
     * @ORM\Column(name="step_order", type="integer")
     */
    protected $order;

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
     * @ORM\OneToMany(targetEntity="Step", mappedBy="parent", indexBy="id")
     */
    protected $children;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description = null;

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
     * @var Innova\PathBundle\Entity\Path\Path
     * 
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Path\Path", inversedBy="steps")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $path;

    /**
     * @ORM\ManyToOne(targetEntity="StepWho", inversedBy="steps")
     * @ORM\JoinColumn(name="stepWho_id", referencedColumnName="id", nullable=true)
     */
    protected $stepWho;

    /**
     * @ORM\ManyToOne(targetEntity="StepWhere", inversedBy="steps")
     * @ORM\JoinColumn(name="stepWhere_id", referencedColumnName="id", nullable=true)
     */
    protected $stepWhere;

    /**
     * @ORM\OneToMany(targetEntity="Step2ResourceNode", mappedBy="step", indexBy="id")
     */
    protected $step2ResourceNodes;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->withTutor = false;
        $this->withComputer = true;
        
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
     * Set description
     * @param  string $description
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * Set order
     * @param  integer $order
     * @return \Innova\PathBundle\Entity\Step
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
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @return \Innova\PathBundle\Entity\
     */
    public function setPath(\Innova\PathBundle\Entity\Path\Path $path = null)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     * @return \Innova\PathBundle\Entity\Path\Path
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
        $this->children->set($step->getId(), $step);
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
        $this->step2ResourceNodes->set($step2ResourceNodes->getId(), $step2ResourceNodes);
        $step2ResourceNodes->setStep($this);
        
        return $this;
    }

    /**
     * Remove step2ResourceNodes
     * @param \Innova\PathBundle\Entity\Step2ResourceNode $step2ResourceNodes
     */
    public function removeStep2ResourceNode(Step2ResourceNode $step2ResourceNodes)
    {
        $this->step2ResourceNodes->removeElement($step2ResourceNodes);
        $step2ResourceNodes->setStep(null);
        
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
                if ($a->getOrder() === $b->getOrder()) {
                    return 0;
                }
                return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
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
