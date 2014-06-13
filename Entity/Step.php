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
     * Activity of this step
     * @var \Claroline\CoreBundle\Entity\Resource\Activity
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\Activity")
     * @ORM\JoinColumn(name="activity_id", referencedColumnName="id")
     */
    protected $activity;

    /**
     * Parameters for this step
     * @var \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Activity\ActivityParameters")
     * @ORM\JoinColumn(name="parameters_id", referencedColumnName="id")
     */
    protected $parameters;

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
     * Path
     * @var \Innova\PathBundle\Entity\Path\Path
     * 
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Path\Path", inversedBy="steps")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $path;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
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
     * Get activity
     * @return \Claroline\CoreBundle\Entity\Resource\Activity
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set activity
     * @param \Claroline\CoreBundle\Entity\Resource\Activity $activity
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setActivity(\Claroline\CoreBundle\Entity\Resource\Activity $activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity parameters
     * @return \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Activity\ActivityParameters $parameters
     * @return \Innova\PathBundle\Entity\Step
     */
    public function setParameters(\Claroline\CoreBundle\Entity\Activity\ActivityParameters $parameters)
    {
        $this->parameters = $parameters;

        return $this;
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
     * Set path
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @return \Innova\PathBundle\Entity\
     */
    public function setPath(Path\Path $path = null)
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
