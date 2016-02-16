<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Manager\RightsManager;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Criterion;
use Innova\PathBundle\Entity\Criteriagroup;
use Innova\PathBundle\Entity\InheritedResource;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\StepCondition;
use Innova\PathBundle\Manager\StepConditionManager;

/**
 * Manage Publishing of the paths
 */
class PublishingManager
{
    /**
     * Current entity manage for data persist
     * @var \Doctrine\Common\Persistence\ObjectManager $om
     */
    protected $om;

    /**
     * Resource Manager
     * @var \Claroline\CoreBundle\Manager\ResourceManager
     */
    protected $resourceManager;

    /**
     * innova step manager
     * @var \Innova\PathBundle\Manager\StepManager
     */
    protected $stepManager;

    /**
     * Rights Manager
     * @var \Claroline\CoreBundle\Manager\RightsManager
     */
    protected $rightsManager;

    /**
     * Path to publish
     * @var \Innova\PathBundle\Entity\Path\Path
     */
    protected $path;

    /**
     * JSON structure of the path
     * @var \stdClass
     */
    protected $pathStructure;

    /**
     * array uniqid => step
     */
    protected $uniqId2step;

    /**
     * array uniqid => stepcondition
     */
    protected $uniqId2sc;

    /**
     * array uniqid => criteriagroup
     */
    protected $uniqId2cg;

    /**
     * array uniqid => criteria
     */
    protected $uniqId2crit;

    /**
     *StepConditions Manager
     * @var \Claroline\CoreBundle\Manager\ResourceManager
     */
    protected $stepConditionManager;

    /**
     * Class constructor
     * @param \Doctrine\Common\Persistence\ObjectManager      $objectManager
     * @param \Innova\PathBundle\Manager\StepManager          $stepManager
     * @param \Innova\PathBundle\Manager\StepConditionManager $stepConditionManager
     * @param \Claroline\CoreBundle\Manager\RightsManager     $rightsManager
     */
    public function __construct(
        ObjectManager        $objectManager,
        StepManager          $stepManager,
        StepConditionManager $stepConditionManager,
        RightsManager        $rightsManager)
    {
        $this->om                   = $objectManager;
        $this->stepManager          = $stepManager;
        $this->stepConditionManager = $stepConditionManager;
        $this->rightsManager        = $rightsManager;
    }

    /**
     * Initialize a new Publishing
     * @param  \Innova\PathBundle\Entity\Path\Path          $path
     * @throws \Exception
     * @return \Innova\PathBundle\Manager\PublishingManager
     */
    protected function start(Path $path)
    {
        // Get the path structure
        $pathStructure = $path->getStructure();
        if (empty($pathStructure)) {
            throw new \Exception('Unable to find JSON structure of the path. Publication aborted.');
        }

        // Decode structure
        $this->pathStructure = json_decode($pathStructure);
        $this->path         = $path;
        $this->uniqId2step  = array();
        $this->uniqId2sc    = array();
        $this->uniqId2cg    = array();
        $this->uniqId2crit  = array();

        return $this;
    }

    /**
     * End of the Publishing
     * Remove temp data from current service
     * @return \Innova\PathBundle\Manager\PublishingManager
     */
    protected function end()
    {
        $this->path          = null;
        $this->pathStructure = null;
        $this->uniqId2step   = array();
        $this->uniqId2sc     = array();
        $this->uniqId2cg     = array();
        $this->uniqId2crit   = array();

        return $this;
    }

    /**
     * Publish path
     * Create all needed Entities from JSON structure created by the Editor
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @throws \Exception
     * @return boolean
     */
    public function publish(Path $path)
    {
        // We need to publish all linked resources to have a full working Path

        // Start Publishing
        $this->start($path);

        // Store existing steps to remove steps which no longer exist
        $existingSteps = $path->getSteps()->toArray();

        // Publish steps for this path
        $toProcess = !empty($this->pathStructure->steps) ? $this->pathStructure->steps : array();

        $publishedSteps = $this->publishSteps(0, null, $toProcess);

        // Clean steps to remove
        $this->cleanSteps($publishedSteps, $existingSteps);

        // flush all steps
        $this->om->flush();

        // replace ids
        $json = $this->replaceStepIds();
        $json = $this->replaceStepConditionId($json);
        $json = $this->replaceCriteriagroupId($json);
        $json = $this->replaceCriteriaId($json);

        // Re encode updated structure and update Path
        $this->path->setStructure($json);

        // Manage rights
        $this->manageRights();

        // Mark Path as published
        $this->path->setPublished(true);
        $this->path->setModified(false);

        // Persist data
        $this->om->persist($this->path);
        $this->om->flush();

        // End Publishing
        $this->end();

        return true;
    }

    protected function replaceStepIds()
    {
        $json = json_encode($this->pathStructure);
        foreach ($this->uniqId2step as $uniqId => $step) {
            $json = str_replace($uniqId, $step->getId(), $json);
        }

        return $json;
    }

    /**
     * Publish steps for the path
     * @param  integer                        $level
     * @param  \Innova\PathBundle\Entity\Step $parent
     * @param  array                          $steps
     * @param  array                          $propagatedResources
     * @return array
     */
    protected function publishSteps($level = 0, Step $parent = null, array $steps = array(), $propagatedResources = array())
    {
        $currentOrder = 0;
        $processedSteps = array();

        // Retrieve existing steps for this path
        $existingSteps = $this->path->getSteps();

        foreach ($steps as $stepStructure) {
            if (empty($stepStructure->resourceId) || !$existingSteps->containsKey($stepStructure->resourceId)) {
                // Current step has never been published or step entity has been deleted => create it
                $step = $this->stepManager->create($this->path, $level, $parent, $currentOrder, $stepStructure);
                $uniqId = "_STEP".uniqid();
                $this->uniqId2step[$uniqId] = $step;
                // Update json structure with new resource ID
                $stepStructure->resourceId = $uniqId;
            } else {
                // Step already exists => update it
                $step = $existingSteps->get($stepStructure->resourceId);
                $step = $this->stepManager->edit($this->path, $level, $parent, $currentOrder, $stepStructure, $step);
            }

            //condition management
            $publishedStepConditions = $this->publishStepConditions($step, $stepStructure);

            // Manage resources inheritance
            $excludedResources = !empty($stepStructure->excludedResources) ? $stepStructure->excludedResources : array();
            $this->publishPropagatedResources($step, $propagatedResources, $excludedResources);

            // Store step to know it doesn't have to be deleted when we will clean the path
            $processedSteps[] = $step;

            // Process children of current step
            if (!empty($stepStructure->children)) {
                // Add propagated resources of current step for children
                $currentPropagatedResources = array();
                if (!empty($stepStructure->resources)) {
                    foreach ($stepStructure->resources as $resource) {
                        if (!empty($resource->propagateToChildren) && $resource->propagateToChildren) {
                            // Resource is propagated
                            $currentPropagatedResources[] = array(
                                'id'         => $resource->id,
                                'resourceId' => $resource->resourceId,
                                'lvl'        => $level,
                            );
                        }
                    }
                }

                $childrenLevel = $level + 1;

                $propagatedResourcesTemp = array_merge($propagatedResources, $currentPropagatedResources);
                $childrenSteps = $this->publishSteps($childrenLevel, $step, $stepStructure->children, $propagatedResourcesTemp);

                // Store children steps
                $processedSteps = array_merge($processedSteps, $childrenSteps);
            }

            $currentOrder++;
        }

        return $processedSteps;
    }

    /**
     * Manage resource inheritance
     * @param  \Innova\PathBundle\Entity\Step               $step
     * @param  array                                        $propagatedResources
     * @param  array                                        $excludedResources
     * @return \Innova\PathBundle\Manager\PublishingManager
     */
    protected function publishPropagatedResources(Step $step, array $propagatedResources = array(), array $excludedResources = array())
    {
        $inheritedResources = array();
        $currentInherited = $step->getInheritedResources();

        if (!empty($propagatedResources)) {
            foreach ($propagatedResources as $resource) {
                if (!in_array($resource['id'], $excludedResources)) {
                    // Resource is not excluded => link it to step

                    // Retrieve resource node
                    $resourceNode = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($resource['resourceId']);

                    if (!$inherited = $step->hasInheritedResource($resourceNode->getId())) {
                        // Inherited resource doesn't exist => Create inherited resource
                        $inherited = new InheritedResource();
                    }

                    // Update inherited resource properties
                    $inherited->setResource($resourceNode);
                    $inherited->setLvl($resource['lvl']);

                    // Add inherited resource to Step
                    $step->addInheritedResource($inherited);

                    $this->om->persist($inherited);

                    // Store resource ID to clean step
                    $inheritedResources[] = $resourceNode->getId();
                }
            }
        }

        // Clean inherited resources which no long exists
        foreach ($currentInherited as $inherited) {
            $resourceId = $inherited->getResource()->getId();
            if (!in_array($resourceId, $inheritedResources)) {
                $step->removeInheritedResource($inherited);
                $this->om->remove($inherited);
            }
        }

        return $this;
    }

    /**
     * Clean steps which no long exist in the current path
     * @param  array                                        $neededSteps
     * @param  array                                        $existingSteps
     * @return \Innova\PathBundle\Manager\PublishingManager
     */
    protected function cleanSteps(array $neededSteps = array(), array $existingSteps = array())
    {
        $toRemove = array_filter($existingSteps, function (Step $current) use ($neededSteps) {
            $removeStep = true;
            foreach ($neededSteps as $step) {
                if ($current->getId() == $step->getId()) {
                    $removeStep = false;
                    break;
                }
            }

            return $removeStep;
        });

        foreach ($toRemove as $stepToRemove) {
            $this->path->removeStep($stepToRemove);
            $this->om->remove($stepToRemove);
        }

        return $this;
    }

    /**
     * Check that all Activities and Resources as at least same rights than the Path
     * @return \Innova\PathBundle\Manager\PublishingManager
     */
    protected function manageRights()
    {
        // Grab Resources and Activities
        $nodes = $this->retrieveAllNodes($this->path->getSteps()->toArray());

        if (!empty($nodes)) {
            $pathRights = $this->path->getResourceNode()->getRights();

            foreach ($nodes as $node) {
                foreach ($pathRights as $right) {
                    if ($right->getMask() & 1) {
                        $this->rightsManager->editPerms($right->getMask(), $right->getRole(), $node, true);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve all ResourceNodes of a Path
     * @param  array $steps
     * @return array
     */
    protected function retrieveAllNodes(array $steps)
    {
        $nodes = array();

        foreach ($steps as $step) {
            $activity = $step->getActivity();
            if (!empty($activity)) {
                // Get Activity Node
                $nodes[] = $activity->getResourceNode();

                // Get Activity primary Resource Node)
                $primaryResource = $activity->getPrimaryResource();
                if (!empty($primaryResource)) {
                    $nodes[] = $primaryResource;
                }

                // Get Activity secondary Resources Nodes
                $parameters = $activity->getParameters();
                if (!empty($parameters)) {
                    $secondaryResources = $parameters->getSecondaryResources();
                    if (!empty($secondaryResources)) {
                        $nodes = array_merge($nodes, $secondaryResources->toArray());
                    }
                }
            }

            // Get Inherited Resources Nodes
            $inheritedResources = $step->getInheritedResources();
            if (!empty($inheritedResources)) {
                foreach ($inheritedResources as $inherited) {
                    $nodes[] = $inherited->getResource();
                }
            }

            $children = $step->getChildren();
            if (!empty($children)) {
                $childrenNodes = $this->retrieveAllNodes($children->toArray());
                $nodes = array_merge($nodes, $childrenNodes);
            }
        }

        return $nodes;
    }

    /**
     * Get a condition by ID
     * @param  integer   $conditionId
     * @return null|StepCondition
     */
    public function getStepCondition($conditionId)
    {
        return $this->om->getRepository("InnovaPathBundle:StepCondition")->findOneById($conditionId);
    }

    /**
     * Get a criteriagroup by ID
     * @param  integer   $criteriagroupId
     * @return null|Criteriagroup
     */
    public function getCriteriagroup($criteriagroupId)
    {
        return $this->om->getRepository("InnovaPathBundle:Criteriagroup")->findOneById($criteriagroupId);
    }

    /**
     * Get a criteria by ID
     * @param  integer   $criterionId
     * @return null|Criterion
     */
    public function getCriteria($criterionId)
    {
        return $this->om->getRepository("InnovaPathBundle:Criterion")->findOneById($criterionId);
    }

    /**
     * Add or update stepconditions
     *
     * @param Step $stepDB
     * @param \stdClass $stepJS
     * @return array
     */
    protected function publishStepConditions(Step $stepDB, \stdClass $stepJS = null)
    {
        //retrieve condition from DB
        $existingCondition = $stepDB->getCondition();
        $processedCondition = array();

        if (!empty($stepJS->condition)) {
            // retrieve the condition
            $conditionJS = $stepJS->condition;

            // Current condition has never been published or condition entity has been deleted => create it
            if (empty($conditionJS->scid) || ($existingCondition->getId() != $conditionJS->scid)) {
                // Create StepCondition
                $publishedCondition = $this->stepConditionManager->createStepCondition($stepDB);
                $uniqId = "_COND".uniqid();
                $this->uniqId2sc[$uniqId] = $publishedCondition;
                // Update json structure with new resource ID
                $conditionJS->scid = $uniqId;
            }
            else {
                // Update CriteriaGroup
                $publishedCondition = $this->getStepCondition($conditionJS->scid);
                $publishedCondition = $this->stepConditionManager->editStepCondition($stepDB, $publishedCondition);
            }

            $processedCondition[] = $publishedCondition;

            //manage criteriagroups
            $existingCriteriagroups = $publishedCondition->getCriteriagroups()->toArray();
            $publishedCriteriagroup = $this->publishCriteriagroups($publishedCondition, 0, null, $conditionJS->criteriagroups);
//echo "Clean criteriagroup to remove <br>\n";
            // Clean criteriagroup to remove
            $this->cleanCriteriagroup($publishedCriteriagroup, $existingCriteriagroups, $publishedCondition);

//echo "Clean Condition to remove <br>\n";
            // Clean Condition to remove
            if (is_object($existingCondition)) {
                $this->cleanCondition($publishedCondition, $existingCondition, $stepDB);
            }
        }

        return $processedCondition;
    }

    /**
     * Update criteriagroups for a condition
     *
     * @param StepCondition $conditionDB
     * @param int $level
     * @param array $criteriagroupsJS
     * @return array
     */
    protected function publishCriteriagroups(StepCondition $conditionDB, $level = 0, Criteriagroup $parentCG = null, array $criteriagroupsJS = array())
    {
        $processedCriteriagroups = array();
        $currentOrder = 0;

        // Retrieve existing criteriagroups for this condition
        $existingCriteriagroups = $conditionDB->getCriteriagroups();

        foreach ($criteriagroupsJS as $criteriagroupJS)
        {
//echo "criteriagroupid ".$criteriagroupJS->id."<br>\n";

            // Current criteriagroup has never been published or criteriagroup entity has been deleted => create it
            if (empty($criteriagroupJS->cgid) || !$existingCriteriagroups->containsKey($criteriagroupJS->cgid))
            {
//echo "create group <br>\n";
                $criteriagroupDB = $this->stepConditionManager->createCriteriagroup($level, $currentOrder, $parentCG, $conditionDB);
                $uniqId = "_CG".uniqid();
                $this->uniqId2cg[$uniqId] = $criteriagroupDB;
                // Update json structure with new resource ID
                $criteriagroupJS->cgid = $uniqId;
            }
            else
            {
//echo "edit group <br>\n";
                //retrieve CG
                $criteriagroupDB = $existingCriteriagroups->get($criteriagroupJS->cgid);
                //edit CG in DB
                $criteriagroupDB = $this->stepConditionManager->editCriteriagroup($level, $currentOrder, $parentCG, $conditionDB, $criteriagroupDB);
            }
//echo "Manage criteria <br>\n";
            // Manage criteria
            $existingCriteria = $criteriagroupDB->getCriteria();
            $publishedCriteria = $this->publishCriteria($criteriagroupJS, $criteriagroupDB);
//echo "Clean criteria to remove <br>\n";
            // Clean criteria to remove
            $this->cleanCriteria($publishedCriteria, $existingCriteria->toArray(), $criteriagroupDB);

            // Store criteriagroup to know it doesn't have to be deleted when we will clean the condition
            $processedCriteriagroups[] = $criteriagroupDB;

            //Check children criteriagroup
            if (!empty($criteriagroupJS->criteriagroup))
            {
                $childrenLevel = $level + 1;
                $childrenCriteriagroups = $this->publishCriteriagroups($conditionDB, $childrenLevel, $criteriagroupDB, $criteriagroupJS->criteriagroup);

                // Store children criteriagroup
                $processedCriteriagroups = array_merge($processedCriteriagroups, $childrenCriteriagroups);
            }

            $currentOrder++;
        }

        return $processedCriteriagroups;
    }

    /**
     * Update criteria from a criteriagroup
     *
     * @param array $criteriagroupJS
     * @param Criteriagroup $criteriagroupDB
     * @return array
     */
    protected function publishCriteria($criteriagroupJS = array(), Criteriagroup $criteriagroupDB)
    {
        $processedCriteria = array();

        // Retrieve existing criteriagroups for this condition
        $existingCriteria = $criteriagroupDB->getCriteria();

        foreach ($criteriagroupJS->criterion as $criterionJS) {
            //criterion attributes
            $data = (isset($criterionJS->data)) ? $criterionJS->data : null;
            $ctype = (isset($criterionJS->type)) ? $criterionJS->type : null;
//echo "criterionid ".$criterionJS->id."<br>\n";
            // Current criterion has never been published or criterion entity has been deleted => create it
            if (empty($criterionJS->critid) || !$existingCriteria->containsKey($criterionJS->critid))
            {
//echo "criterion add <br>\n";
                $criterionDB = $this->stepConditionManager->createCriterion($data, $ctype, $criteriagroupDB);
                $uniqId = "_CRIT".uniqid();
                $this->uniqId2crit[$uniqId] = $criterionDB;
                // Update json structure with new resource ID
                $criterionJS->critid = $uniqId;
            } else {
//echo "criterion edit <br>\n";
                //retrieve criterion
                $criterionDB = $existingCriteria->get($criterionJS->critid);
                //edit criterion in DB
                $criterionDB = $this->stepConditionManager->editCriterion($data, $ctype, $criteriagroupDB, $criterionDB);
            }
            // Store criteria to know it doesn't have to be deleted when we will clean the condition
            $processedCriteria[] = $criterionDB;
        }

        return $processedCriteria;
    }

    /**
     * Clean conditions data which no long exist in the current path
     *
     * @param array $neededCondition
     * @param array $existingCondition
     * @param Step $step
     * @return PublishingManager
     */
    protected function cleanCondition($neededCondition = null, $existingCondition = null, Step $step)
    {
//echo "inside cleanCondition<br>\n";
//echo "typeof(existingData)";var_dump(is_object($existingCondition));//echo "<br>\ntypeof(neededData)";var_dump(is_object($neededCondition));
        if ($existingCondition->getId() != $neededCondition->getId()) {
            $step->setCondition(null);
            $this->om->remove($neededCondition);
        }

        return $this;
    }

    /**
     * Clean criteriagroups data which no long exist in the current condition
     *
     * @param array $neededCriteriagroup
     * @param array $existingCriteriagroup
     * @param StepCondition $stepCondition
     * @return PublishingManager
     */
    protected function cleanCriteriagroup(array $neededCriteriagroup = array(), array $existingCriteriagroup = array(), StepCondition $stepCondition)
    {
//echo "inside cleanCriteriagroup<br>\n";
        $toRemove = array_filter($existingCriteriagroup, function (Criteriagroup $current) use ($neededCriteriagroup) {
//echo "inside cleanCriteriagroup closure<br>\n";
            $removeCriteriagroup = true;
            foreach ($neededCriteriagroup as $data) {
                if ($current->getId() == $data->getId()) {
                    $removeCriteriagroup = false;
                    break;
                }
            }

            return $removeCriteriagroup;
        });

        foreach ($toRemove as $criteriagroupToRemove) {
            $stepCondition->removeCriteriagroup($criteriagroupToRemove);
            $this->om->remove($criteriagroupToRemove);
        }

        return $this;
    }

    /**
     * Clean criteria data which no long exist in the current criteriagroup
     *
     * @param array $neededCriteria
     * @param array $existingCriteria
     * @param Criteriagroup $criteriagroup
     * @return PublishingManager
     */
    protected function cleanCriteria(array $neededCriteria = array(), array $existingCriteria = array(), Criteriagroup $criteriagroup)
    {
//echo "inside cleanCriteria<br>\n";
        $toRemove = array_filter($existingCriteria, function (Criterion $current) use ($neededCriteria) {
//echo "inside cleanCriteria closure<br>\n";
            $removeCriterion = true;
            foreach ($neededCriteria as $data) {
                if ($current->getId() == $data->getId()) {
                    $removeCriterion = false;
                    break;
                }
            }

            return $removeCriterion;
        });

        foreach ($toRemove as $criterionToRemove) {
            $criteriagroup->removeCriterion($criterionToRemove);
            $this->om->remove($criterionToRemove);
        }

        return $this;
    }

    protected function replaceStepConditionId($json)
    {
        foreach ($this->uniqId2sc as $uniqId => $stepcondition) {
            $json = str_replace($uniqId, $stepcondition->getId(), $json);
        }
        return $json;
    }

    protected function replaceCriteriagroupId($json)
    {
        foreach ($this->uniqId2cg as $uniqId => $criteriagroup) {
            $json = str_replace($uniqId, $criteriagroup->getId(), $json);
        }
        return $json;
    }

    protected function replaceCriteriaId($json)
    {
        foreach ($this->uniqId2crit as $uniqId => $criterion) {
            $json = str_replace($uniqId, $criterion->getId(), $json);
        }
        return $json;
    }
}
