<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\ResourceManager;

use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step2ResourceNode;
use Symfony\Component\Security\Core\SecurityContextInterface;

class StepManager
{
    /**
     * 
     * @var \Doctrine\Common\Persistence\ObjectManager $om
     */
    protected $om;

    /**
     * Security Context
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $security;

    /**
     * Resource Manager
     * @var \Claroline\CoreBundle\Manager\ResourceManager
     */
    protected $resourceManager;

    /**
     * Class constructor
     * @param \Doctrine\Common\Persistence\ObjectManager $om
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $security
     * @param \Claroline\CoreBundle\Manager\ResourceManager $resourceManager
     */
    public function __construct(
        ObjectManager $om,
        SecurityContextInterface $security,
        ResourceManager     $resourceManager)
    {
        $this->om              = $om;
        $this->security        = $security;
        $this->resourceManager = $resourceManager;
    }

    public function editResourceNodeRelation(Step $step, $resourceNodeId, $excluded, $propagated, $order = null)
    {
        $step2resourceNode = $this->om->getRepository('InnovaPathBundle:Step2ResourceNode')->findOneBy(array (
            'step' => $step,
            'resourceNode' => $resourceNodeId,
            'excluded' => $excluded,
        ));
    
        if (!$step2resourceNode) {
            $step2resourceNode = new Step2ResourceNode();
        }
    
        $step2resourceNode->setResourceNode($this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($resourceNodeId));
        $step2resourceNode->setStep($step);
        $step2resourceNode->setExcluded($excluded);
        $step2resourceNode->setPropagated($propagated);
        
        if (!empty($order)) {
            $step2resourceNode->setResourceOrder($order);
        }
    
        $this->om->persist($step2resourceNode);
        $this->om->flush();
    
        return $step2resourceNode;
    }
    
    /**
     * Create a new step from JSON structure
     * @param  \Innova\PathBundle\Entity\Path\Path $path          Parent path of the step
     * @param  integer                             $level         Depth of the step in the path
     * @param  \Innova\PathBundle\Entity\Step      $parent        Parent step of the step
     * @param  integer                             $order         Order of the step relative to its siblings
     * @param  \stdClass                           $stepStructure Data about the step
     * @return \Innova\PathBundle\Entity\Step                     Edited step
     */
    public function create(Path $path, $level = 0, Step $parent = null, $order = 0, \stdClass $stepStructure)
    {
        $step = new Step();
        
        return $this->edit($path, $level, $parent, $order, $stepStructure, $step);
    }
    
    /**
     * Update an existing step from JSON structure
     * @param  \Innova\PathBundle\Entity\Path\Path $path          Parent path of the step
     * @param  integer                             $level         Depth of the step in the path
     * @param  \Innova\PathBundle\Entity\Step      $parent        Parent step of the step
     * @param  integer                             $order         Order of the step relative to its siblings
     * @param  \stdClass                           $stepStructure Data about the step
     * @param  \Innova\PathBundle\Entity\Step      $step          Current step to edit
     * @return \Innova\PathBundle\Entity\Step                     Edited step
     */
    public function edit(Path $path, $level = 0, Step $parent = null, $order = 0, \stdClass $stepStructure, Step $step)
    {
        // Update step properties
        $step->setPath($path);
        $step->setParent($parent);
        $step->setLvl($level);
        $step->setOrder($order);
        
        // Grab data from structure
        $name = !empty($stepStructure->name) ? $stepStructure->name : Step::DEFAULT_NAME;
        $step->setName($name);
        
        $this->updateActivity($step, $stepStructure);
        $this->updateParameters($step, $stepStructure);
        
        // Save modifications
        $this->om->persist($step);
        $this->om->flush();
        
        return $step;
    }

    /**
     * @param \Innova\PathBundle\Entity\Step $step
     * @param  \stdClass                     $stepStructure
     * @return \Innova\PathBundle\Manager\PublishingManager
     * @throws \LogicException
     */
    public function updateActivity(Step $step, \stdClass $stepStructure)
    {
        $newActivity = false;
        $activity = $step->getActivity();
        if (empty($activity)) {
            if (!empty($stepStructure->activityId)) {
                // Load activity from DB
                $activity = $this->om->getRepository('ClarolineCoreBundle:Resource\Activity')->findById($stepStructure->activityId);
                if (empty($activity)) {
                    // Can't find Activity
                    throw new \LogicException('Unable to find Activity referenced by ID : ' . $stepStructure->activityId);
                }
            }
            else {
                // Create new activity
                $newActivity = true;
                $activity = new Activity();
            }
        }

        // Update activity properties
        if (!empty($stepStructure->name)) {
            $name = $stepStructure->name;
        }
        else {
            // Create a default name
            $name = $step->getPath()->getName() . ' - ' . Step::DEFAULT_NAME . ' ' . $step->getOrder();
        }
        $step->setName($name);

        $description = !empty($stepStructure->description) ? $stepStructure->description : null;
        $activity->setDescription($description);

        // Link resource if needed
        if (!empty($stepStructure->primaryResource) && !empty($stepStructure->primaryResource->resourceId)) {
            $resource = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findById($stepStructure->primaryResource->resourceId);
            if (!empty($resource)) {
                $activity->setPrimaryResource($resource);
            }
            else {
                // Resource not found
                throw new \LogicException('Unable to find ResourceNode referenced by ID : ' . $stepStructure->primaryResource->resourceId);
            }
        }

        // Generate Claroline resource
        if ($newActivity) {
            $activityType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findByName('activity');
            $currentUser = $this->security->getToken()->getUser();
            $workspace = $step->getWorkspace();
            $activity = $this->resourceManager->create($activity, $activityType, $currentUser, $workspace);
        }

        // Update JSON structure
        $stepStructure->activityId = $activity->getId();

        // Store Activity in Step
        $step->setActivity($activity);

        return $this;
    }

    public function updateParameters(Step $step, \stdClass $stepStructure)
    {
        $parameters = $step->getParameters();
        if (empty($parameters)) {
            $parameters = new ActivityParameters();
        }

        // Update parameters properties
        $withTutor = !empty($stepStructure->withTutor) ? $stepStructure->withTutor : false;
        $parameters->setWithTutor($withTutor);

        $durationHours = !empty($stepStructure->durationHours) ? intval($stepStructure->durationHours) : 0;
        $durationMinutes = !empty($stepStructure->durationMinutes) ? intval($stepStructure->durationMinutes) : 0;
        $seconds = $durationHours * 3600 + $durationMinutes * 60;
        $parameters->setMaxDuration($seconds);

        $who = !empty($stepStructure->who) ? $stepStructure->who : null;
        $parameters->setWho($who);

        $where = !empty($stepStructure->where) ? $stepStructure->where : null;
        $parameters->setWhere($where);

        // Set resources

        // Persist parameters to generate ID
        $this->om->persist($parameters);
        $this->om->flush();

        // Store parameters in Step
        $step->setParameters($parameters);

        return $this;
    }

    public function contextualUpdate($step)
    {
        $path = $step->getPath();
        $json = json_decode($path->getStructure());
        $json_root_steps = $json->steps;

        $this->findAndUpdateJsonStep($json_root_steps, $step);

        $json = json_encode($json);
        $path->setStructure($json);
       
        $this->om->persist($path);
        $this->om->persist($step);
        $this->om->flush();
    }

    public function findAndUpdateJsonStep($jsonSteps, $step)
    {
        foreach($jsonSteps as $jsonStep){
            echo $jsonStep->resourceId;
            if ($jsonStep->resourceId == $step->getId()){
                $jsonStep->description = $step->getDescription();
            }
            else if (!empty($jsonStep->children)) {
                $this->findAndUpdateJsonStep($jsonStep->children, $step);
            }
        }
    }
}
