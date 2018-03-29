<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Manager\ResourceManager;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\InheritedResource;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("innova_path.manager.step")
 */
class StepManager
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * StepManager constructor.
     *
     * @DI\InjectParams({
     *     "om"                   = @DI\Inject("claroline.persistence.object_manager"),
     *     "session"              = @DI\Inject("session"),
     *     "translator"           = @DI\Inject("translator"),
     *     "resourceManager"      = @DI\Inject("claroline.manager.resource_manager"),
     * })
     *
     * @param ObjectManager       $om
     * @param SessionInterface    $session
     * @param TranslatorInterface $translator
     * @param ResourceManager     $resourceManager
     */
    public function __construct(
        ObjectManager        $om,
        SessionInterface     $session,
        TranslatorInterface  $translator,
        ResourceManager      $resourceManager)
    {
        $this->om = $om;
        $this->session = $session;
        $this->translator = $translator;
        $this->resourceManager = $resourceManager;
    }

    /**
     * Get a step by ID.
     *
     * @param int $stepId
     *
     * @return object|Step
     */
    public function get($stepId)
    {
        return $this->om->getRepository('InnovaPathBundle:Step')->find($stepId);
    }

    /**
     * Create a new step from JSON structure.
     *
     * @param Path      $path          Parent path of the step
     * @param int       $level         Depth of the step in the path
     * @param Step      $parent        Parent step of the step
     * @param int       $order         Order of the step relative to its siblings
     * @param \stdClass $stepStructure Data about the step
     *
     * @return Step Edited step
     */
    public function create(Path $path, $level, Step $parent = null, $order, \stdClass $stepStructure)
    {
        $step = new Step();

        return $this->edit($path, $level, $parent, $order, $stepStructure, $step);
    }

    /**
     * Update an existing step from JSON structure.
     *
     * @param Path      $path          Parent path of the step
     * @param int       $level         Depth of the step in the path
     * @param Step      $parent        Parent step of the step
     * @param int       $order         Order of the step relative to its siblings
     * @param \stdClass $stepStructure Data about the step
     * @param Step      $step          Current step to edit
     *
     * @return Step Edited step
     */
    public function edit(Path $path, $level, Step $parent = null, $order, \stdClass $stepStructure, Step $step)
    {
        // Update step properties
        $step->setPath($path);
        $step->setParent($parent);
        $step->setLvl($level);
        $step->setOrder($order);

        $height = $stepStructure->activityHeight ? $stepStructure->activityHeight : 0;
        $step->setActivityHeight($height);

        // Update related Activity
        $this->updateParameters($step, $stepStructure);
        $this->updateActivity($step, $stepStructure);

        // Save modifications
        $this->om->persist($step);

        return $step;
    }

    /**
     * Update or Create the Activity linked to the Step.
     *
     * @param Step      $step
     * @param \stdClass $stepStructure
     *
     * @throws \LogicException
     */
    public function updateActivity(Step $step, \stdClass $stepStructure)
    {
        $newActivity = false;
        $activity = $step->getActivity();
        if (empty($activity)) {
            if (!empty($stepStructure->activityId)) {
                // Load activity from DB
                $activity = $this->om->getRepository('ClarolineCoreBundle:Resource\Activity')->find($stepStructure->activityId);
                if (empty($activity)) {
                    // Can't find Activity => create a new one
                    $newActivity = true;
                    $activity = new Activity();
                }
            } else {
                // Create new activity
                $newActivity = true;
                $activity = new Activity();
            }
        }

        // Update activity properties
        if (!empty($stepStructure->name)) {
            $name = $stepStructure->name;
        } else {
            // Create a default name
            $name = 'Step '.$step->getOrder();
        }
        $activity->setName($name);
        $activity->setTitle($name);

        $description = !empty($stepStructure->description) ? $stepStructure->description : ' ';
        $activity->setDescription($description);

        // Link resource if needed
        if (!empty($stepStructure->primaryResource) && !empty($stepStructure->primaryResource[0]) && !empty($stepStructure->primaryResource[0]->resourceId)) {
            $resource = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->find($stepStructure->primaryResource[0]->resourceId);
            if (!empty($resource)) {
                $activity->setPrimaryResource($resource);
            } else {
                $warning = $this->translator->trans('warning_primary_resource_deleted', ['resourceId' => $stepStructure->primaryResource[0]->resourceId, 'resourceName' => $stepStructure->primaryResource[0]->name]);
                $this->session->getFlashBag()->add('warning', $warning);
                $stepStructure->primaryResource = [];
            }
        } elseif ($activity->getPrimaryResource()) {
            // Step had a resource which has been deleted
            $activity->setPrimaryResource(null);
        }

        // Generate Claroline resource node and rights
        if ($newActivity) {
            // It's a new Activity, so use Step parameters
            $activity->setParameters($step->getParameters());

            /** @var ResourceType $activityType */
            $activityType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy([
                'name' => 'activity',
            ]);
            $creator = $step->getPath()->getCreator();
            $workspace = $step->getWorkspace();

            // Store Activity in same directory than parent Path
            $parent = $step->getPath()->getResourceNode()->getParent();
            if (empty($parent)) {
                $parent = $this->resourceManager->getWorkspaceRoot($workspace);
            }

            $activity = $this->resourceManager->create($activity, $activityType, $creator, $workspace, $parent);
        } else {
            // Activity already exists => update ResourceNode
            $activity->getResourceNode()->setName($activity->getTitle());
        }

        // Store Activity in Step
        $step->setActivity($activity);
    }

    /**
     * Update parameters of the Step.
     *
     * @param Step      $step
     * @param \stdClass $stepStructure
     *
     * @return $this
     */
    public function updateParameters(Step $step, \stdClass $stepStructure)
    {
        $parameters = $step->getParameters();
        if (empty($parameters)) {
            $parameters = new ActivityParameters();
        }

        // Update parameters properties
        $duration = !empty($stepStructure->duration) ? $stepStructure->duration : null;
        $parameters->setMaxDuration($duration);

        $withTutor = !empty($stepStructure->withTutor) ?
            filter_var($stepStructure->withTutor, FILTER_VALIDATE_BOOLEAN) :
            false;
        $parameters->setWithTutor($withTutor);

        $who = !empty($stepStructure->who) ? $stepStructure->who : null;
        $parameters->setWho($who);

        $where = !empty($stepStructure->where) ? $stepStructure->where : null;
        $parameters->setWhere($where);

        $evaluationType = !empty($stepStructure->evaluationType) ? $stepStructure->evaluationType : null;
        $parameters->setEvaluationType($evaluationType);

        // Set resources
        $this->updateSecondaryResources($parameters, $stepStructure);

        // Persist parameters to generate ID
        $this->om->persist($parameters);

        // Store parameters in Step
        $step->setParameters($parameters);

        return $this;
    }

    /**
     * Update secondary Resources of the Step.
     *
     * @param ActivityParameters $parameters
     * @param \stdClass          $stepStructure
     *
     * @return $this
     */
    public function updateSecondaryResources(ActivityParameters $parameters, \stdClass $stepStructure)
    {
        // Store current resources to clean removed
        $existingResources = $parameters->getSecondaryResources();
        $existingResources = $existingResources->toArray();

        // Publish new resources
        $publishedResources = [];
        if (!empty($stepStructure->resources)) {
            $i = 0;
            foreach ($stepStructure->resources as $index => $resource) {
                /** @var ResourceNode $resourceNode */
                $resourceNode = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->find($resource->resourceId);
                if (!empty($resourceNode)) {
                    $parameters->addSecondaryResource($resourceNode);
                    $publishedResources[] = $resourceNode;
                } else {
                    // Resource has been deleted => remove the reference in path
                    $warning = $this->translator->trans('warning_compl_resource_deleted', ['resourceId' => $resource->resourceId, 'resourceName' => $resource->name]);
                    $this->session->getFlashBag()->add('warning', $warning);

                    unset($stepStructure->resources[$index]);
                }

                ++$i;
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

    /**
     * Import a Step.
     *
     * @param Path  $path
     * @param array $data
     * @param array $createdResources
     * @param array $createdSteps
     *
     * @return array
     */
    public function import(Path $path, array $data, array $createdResources = [], array $createdSteps = [])
    {
        $step = new Step();

        $step->setPath($path);
        if (!empty($data['parent'])) {
            $step->setParent($createdSteps[$data['parent']]);
        }

        $step->setLvl($data['lvl']);
        $step->setOrder($data['order']);
        $step->setActivityHeight(0);

        // Link Step to its Activity
        if (!empty($data['activityNodeId']) && !empty($createdResources[$data['activityNodeId']])) {
            // Step has an Activity
            $step->setActivity($createdResources[$data['activityNodeId']]);
        }

        if (!empty($data['inheritedResources'])) {
            foreach ($data['inheritedResources'] as $inherited) {
                if (!empty($createdResources[$inherited['resource']])) {
                    // Check if the resource has been created (in case of the Resource has no Importer, it may not exist)
                    $inheritedResource = new InheritedResource();
                    $inheritedResource->setLvl($inherited['lvl']);
                    $inheritedResource->setStep($step);
                    $inheritedResource->setResource($createdResources[$inherited['resource']]->getResourceNode());

                    $this->om->persist($inheritedResource);
                }
            }
        }

        $createdSteps[$data['uid']] = $step;

        $this->om->persist($step);

        return $createdSteps;
    }

    /**
     * Transform Step data to export it.
     *
     * @param \Innova\PathBundle\Entity\Step $step
     *
     * @return array
     */
    public function export(Step $step)
    {
        $parent = $step->getParent();
        $activity = $step->getActivity();

        $data = [
            'uid' => $step->getId(),
            'parent' => !empty($parent) ? $parent->getId() : null,
            'activityId' => !empty($activity) ? $activity->getId() : null,
            'activityNodeId' => !empty($activity) ? $activity->getResourceNode()->getId() : null,
            'order' => $step->getOrder(),
            'lvl' => $step->getLvl(),
            'inheritedResources' => [],
        ];

        $inheritedResources = $step->getInheritedResources();
        foreach ($inheritedResources as $inherited) {
            $data['inheritedResources'][] = [
                'resource' => $inherited->getResource()->getId(),
                'lvl' => $inherited->getLvl(),
            ];
        }

        return $data;
    }
}
