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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;


class StepManager
{
    /**
     * Current session
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * Translation manager
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

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
     * @param \Doctrine\Common\Persistence\ObjectManager                $om
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $security
     * @param \Claroline\CoreBundle\Manager\ResourceManager             $resourceManager
     */
    public function __construct(
        ObjectManager            $om,
        SecurityContextInterface $security,
        ResourceManager          $resourceManager,
        SessionInterface         $session,
        TranslatorInterface      $translator)
    {
        $this->om              = $om;
        $this->security        = $security;
        $this->resourceManager = $resourceManager;
        $this->session           = $session;
        $this->translator        = $translator;
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

        $this->updateParameters($step, $stepStructure);
        $this->updateActivity($step, $stepStructure);

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
                $activity = $this->om->getRepository('ClarolineCoreBundle:Resource\Activity')->findOneById($stepStructure->activityId);
                if (empty($activity)) {
                    // Can't find Activity => create a new one
                    $newActivity = true;
                    $activity = new Activity();
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
        $activity->setName($name);
        $activity->setTitle($name);

        $description = $stepStructure->description ?: ' ';
        $activity->setDescription($description);

        // Link resource if needed
        if (!empty($stepStructure->primaryResource) && !empty($stepStructure->primaryResource->resourceId)) {
            $resource = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($stepStructure->primaryResource->resourceId);
            if (!empty($resource)) {
                $activity->setPrimaryResource($resource);
            }
            else {
                $warning = $this->translator->trans('warning_primary_resource_deleted', array('resourceId'=>$stepStructure->primaryResource->resourceId, 'resourceName'=>$stepStructure->primaryResource->name), "innova_tools");
                $this->session->getFlashBag()->add('warning',$warning);
                $stepStructure->primaryResource = null;
            }
        }

        // Generate Claroline resource node and rights
        if ($newActivity) {
            // It's a new Activity, so use Step parameters
            $activity->setParameters($step->getParameters());

            $activityType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('activity');
            $creator = $step->getPath()->getCreator();
            $workspace = $step->getWorkspace();

            // Store Activity in same directory than parent Path
            $parent = $step->getPath()->getResourceNode()->getParent();
            if (empty($parent)) {
                $parent = $this->resourceManager->getWorkspaceRoot($workspace);
            }

            $activity = $this->resourceManager->create($activity, $activityType, $creator, $workspace, $parent);
        }
        else {
            // Activity already exists => update ResourceNode
            $activity->getResourceNode()->setName($activity->getTitle());
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
        $this->updateSecondaryResources($parameters, $stepStructure);

        // Persist parameters to generate ID
        $this->om->persist($parameters);
        $this->om->flush();

        // Store parameters in Step
        $step->setParameters($parameters);

        return $this;
    }

    public function updateSecondaryResources(ActivityParameters $parameters, \stdClass $stepStructure)
    {
        // Store current resources to clean removed
        $existingResources = $parameters->getSecondaryResources();
        $existingResources = $existingResources->toArray();

        // Publish new resources
        $publishedResources = array ();
        if (!empty($stepStructure->resources)) {
            $i = 0;
            foreach ($stepStructure->resources as $resource) {

                $resourceNode = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($resource->resourceId);
                if (!empty($resourceNode)) {
                    $parameters->addSecondaryResource($resourceNode);
                    $publishedResources[] = $resourceNode;
                }
                else {
                    $warning = $this->translator->trans('warning_compl_resource_deleted', array('resourceId'=>$resource->resourceId, 'resourceName'=>$resource->name), "innova_tools");
                    $this->session->getFlashBag()->add('warning',$warning);
                    unset($stepStructure->resources[$i]);
                }
                $i++;
            }
        }

        // Clean removed resources
        foreach ($existingResources as $existingResource) {
            if (!in_array($existingResource, $publishedResources)) {
                $parameters->removeSecondaryResource($existingResource);
            }
        }

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
