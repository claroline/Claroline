<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Activity\ActivityParameters;

/**
 * Step
 *
 * @ORM\Table("innova_step")
 * @ORM\Entity
 */
class Step implements \JsonSerializable
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
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\Activity", cascade={"persist"})
     * @ORM\JoinColumn(name="activity_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $activity;

    /**
     * Parameters for this step
     * @var \Claroline\CoreBundle\Entity\Activity\ActivityParameters
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Activity\ActivityParameters", cascade={"persist"})
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
     * @ORM\OneToMany(targetEntity="Step", mappedBy="parent", indexBy="id", cascade={"persist", "remove"})
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $children;

    /**
     * Path
     * @var \Innova\PathBundle\Entity\Path\Path
     * 
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Path\Path", inversedBy="steps")
     */
    protected $path;

    /**
     * Inherited resources
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Innova\PathBundle\Entity\InheritedResource", mappedBy="step", indexBy="id", cascade={"persist", "remove"})
     * @ORM\OrderBy({"lvl" = "ASC"})
     */
    protected $inheritedResources;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->children           = new ArrayCollection();
        $this->inheritedResources = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getId() . ' - ' . $this->getName();
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
    public function setActivity(Activity $activity)
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
    public function setParameters(ActivityParameters $parameters)
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
        if ($parent != $this->parent) {
            $this->parent = $parent;
            $parent->addChild($this);
        }

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

    public function hasChildren()
    {
        return !empty($this->children) && 0 < $this->children->count();
    }

    /**
     * Add new child to the step
     * @param \Innova\PathBundle\Entity\Step $step
     * @return \Innova\PathBundle\Entity\Step
     */
    public function addChild(Step $step)
    {
        if (!$this->children->contains($step)) {
            $this->children->add($step);
            $step->setParent($this);
        }
        
        return $this;
    }
    
    /**
     * Remove a step from children
     * @param \Innova\PathBundle\Entity\Step $step
     * @return \Innova\PathBundle\Entity\Step
     */
    public function removeChild(Step $step) 
    {
        if ($this->children->contains($step)) {
            $this->children->removeElement($step);
            $step->setParent(null);
        }
        
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
            $sortSiblings = function (Step $a, Step $b) {
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
            $sortParents = function (Step $a, Step $b) {
                if ($a->getLvl() === $b->getLvl()) {
                    return 0;
                }
                return ($a->getLvl() < $b->getLvl()) ? -1 : 1;
            };
            
            uasort($parents, $sortParents);
        }
        
        return $parents;
    }

    /**
     * Wrapper to access workspace of the Step
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace
     */
    public function getWorkspace()
    {
        $workspace = null;
        if (!empty($this->path)) {
            $workspace = $this->path->getWorkspace();
        }

        return $workspace;
    }

    /**
     * Wrapper to access Activity name
     * @return string
     */
    public function getName()
    {
        if (!empty($this->activity)) {
            return $this->activity->getResourceNode()->getName();
        }
        else {
            return '';
        }
    }

    /**
     * Wrapper to access Activity description
     * @return string
     */
    public function getDescription()
    {
        if (!empty($this->activity) && ' ' != $this->activity->getDescription()) {
            return $this->activity->getDescription();
        }
        else {
            return '';
        }
    }

    /**
     * Is current step the root of its own Path ?
     * @return bool
     */
    public function isRoot()
    {
        return null === $this->parent;
    }

    public function getPrevious()
    {
        $previous = null;

        // If current step is the Root of the Tree, there is no previous step
        if (!$this->isRoot()) {
            $siblings = array_values($this->parent->getChildren()->toArray());

            $currentIndex = array_search($this, $siblings, true);
            if (false !== $currentIndex && !empty($siblings[$currentIndex - 1])) {
                $previous = $siblings[$currentIndex - 1];
            } else {
                // Previous is empty so current step has no sibling previous it => so previous is parent
                $previous = $this->parent;
            }
        }

        return $previous;
    }

    public function getNext()
    {
        $next = null;

        if ($this->children->count() > 0) {
            // Get the first child as next step
            $next = $this->children->first();
        } else if (!$this->isRoot()) {
            // Ascend to parent to current step siblings
            $siblings = array_values($this->parent->getChildren()->toArray());

            $currentIndex = array_search($this, $siblings, true);
            if (false !== $currentIndex && !empty($siblings[$currentIndex + 1])) {
                $next = $siblings[$currentIndex + 1];
            } else {
                // Next is empty so current step has no sibling after it

                // TODO
            }
        }

        return $next;
    }

    /**
     * Get inherited resources
     * @return ArrayCollection
     */
    public function getInheritedResources()
    {
        return $this->inheritedResources;
    }

    /**
     * Add an inherited resource
     * @param InheritedResource $inheritedResource
     * @return $this
     */
    public function addInheritedResource(InheritedResource $inheritedResource)
    {
        if (!$this->inheritedResources->contains($inheritedResource)) {
            $this->inheritedResources->add($inheritedResource);
        }

        $inheritedResource->setStep($this);

        return $this;
    }

    /**
     * Remove an inherited resource
     * @param InheritedResource $inheritedResource
     * @return $this
     */
    public function removeInheritedResource(InheritedResource $inheritedResource)
    {
        if ($this->inheritedResources->contains($inheritedResource)) {
            $this->inheritedResources->removeElement($inheritedResource);
        }

        $inheritedResource->setStep(null);

        return $this;
    }

    /**
     * Check if the step is already link to resource
     * @param integer $resourceId
     * @return boolean
     */
    public function hasInheritedResource($resourceId)
    {
        $result = false;

        if (!empty($this->inheritedResources)) {
            foreach ($this->inheritedResources as $inherited) {
                $resource = $inherited->getResource();
                if ($resource->getId() === $resourceId) {
                    $result = $inherited;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Get propagated resources of the Step and inherited from the specified level
     * @param int $lvl
     * @return array
     */
    public function getPropagatedResources($lvl = 0)
    {
        $propagated = array ();
        foreach ($this->children as $child) {
            if ($child->getLvl() > $lvl) {
                // Loop over child inherited resources and grab inherited from `$lvl`
                $inheritedResources = $child->getInheritedResources();
                foreach ($inheritedResources as $inherited) {
                    if ($inherited->getLvl() == $lvl) {
                        // Resource is inherited from the searched level => get it
                        $propagated[] = $inherited->getResource()->getId();
                    }
                }
            }

            // Jump to children
            if ($child->hasChildren()) {
                $childrenPropagated = $child->getPropagatedResources($lvl);
                if (!empty($childrenPropagated)) {
                    $propagated = array_merge($propagated, $childrenPropagated);
                }
            }
        }

        $propagated = array_unique($propagated);

        return $propagated;
    }

    public function getParentsSecondaryResources()
    {
        $resources = array ();

        if (!empty($this->parent)) {
            if (!empty($this->parent->parameters)) {
                $parameters = $this->parent->parameters;
            } else if (!empty($this->parent->activity)) {
                $parameters = $this->parent->activity->getParameters();
            }

            if (!empty($parameters)) {
                $resources = $parameters->getSecondaryResources();
            }

            // Jump to parent
            $parentResources = $this->parent->getParentsSecondaryResources();
            if (!empty($parentResources)) {
                $resources = array_merge($resources, $parentResources);
            }
        }

        return $resources;
    }

    public function jsonSerialize()
    {
        // Initialize data array
        $jsonArray = array (
            'id'                => $this->id,               // A local ID for the step in the path (reuse step ID)
            'resourceId'        => $this->id,               // The real ID of the Step into the DB
            'activityId'        => null,
            'lvl'               => $this->lvl,              // The depth of the step in the path structure
            'name'              => $this->getName(),        // The name of the linked Activity (used as Step name)
            'description'       => $this->getDescription(), // The description of the linked Activity (used as Step description)
            'primaryResource'   => null,
            'resources'         => array (),
            'excludedResources' => array (),
            'children'          => array (),
            'withTutor'         => false,
            'who'               => null,
            'where'             => null,
            'duration'          => null, // Duration in seconds
        );

        // Get activity properties
        if (!empty($this->activity)) {
            // Get activity ID
            $jsonArray['activityId']  = $this->activity->getId(); // The ID of the linked Activity

            // Get primary resource
            $primaryResource = $this->activity->getPrimaryResource();
            if (!empty($primaryResource)) {
                $jsonArray['primaryResource'] = array (
                    'id'         => $primaryResource->getId(),
                    'resourceId' => $primaryResource->getId(),
                    'name'       => $primaryResource->getName(),
                    'type'       => $primaryResource->getMimeType(),
                );
            }
        }

        // Get parameters
        if (!empty($this->parameters)) {
            // Get parameters of the step
            $parameters = $this->parameters;
        } else if (!empty($this->activity)) {
            // Get parameters of the Activity
            $parameters = $this->activity->getParameters();
        }

        if (!empty($parameters)) {
            // Secondary resources
            $secondaryResources = $parameters->getSecondaryResources();
            if (!empty($secondaryResources)) {
                // Get propagated resources of the current step
                $propagatedResources = $this->getPropagatedResources($this->lvl);

                foreach ($secondaryResources as $secondaryResource) {
                    $jsonArray['resources'][] = array(
                        'id'                  => $secondaryResource->getId(),
                        'resourceId'          => $secondaryResource->getId(),
                        'name'                => $secondaryResource->getName(),
                        'type'                => $secondaryResource->getMimeType(),
                        'propagateToChildren' => in_array($secondaryResource->getId(), $propagatedResources),
                    );
                }
            }

            // Global Parameters
            $jsonArray['withTutor'] = $parameters->isWithTutor();
            $jsonArray['who']       = $parameters->getWho();
            $jsonArray['where']     = $parameters->getWhere();
            $jsonArray['duration']  = $parameters->getMaxDuration(); // Duration in seconds
        }

        // Excluded resources
        $parentResources = $this->getParentsSecondaryResources();
        foreach ($parentResources as $resource) {
            $exist = false;
            foreach ($this->inheritedResources as $inherited) {
                if ($inherited->getResource()->getId() == $resource->getId()) {
                    $exist = true;
                    break;
                }
            }

            // Parent resource not found in step
            if (!$exist) {
                $jsonArray['excludedResources'][] = $resource->getId();
            }
        }

        // Get step children
        if (!empty($this->children)) {
            $jsonArray['children'] = array_values($this->children->toArray());
        }

        return $jsonArray;
    }
}
