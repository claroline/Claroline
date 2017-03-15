<?php

namespace Innova\PathBundle\Manager\Condition;

use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Criteriagroup;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\StepCondition;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * StepConditionManager
 * Manages access conditions to the Steps of a Path.
 *
 * @DI\Service("innova_path.manager.condition")
 *
 * @todo : there are problems with creation/update of criteria
 */
class StepConditionManager
{
    /**
     * Object manager.
     *
     * @var ObjectManager
     */
    protected $om;

    /**
     * Criteria Manager.
     *
     * @var CriteriaManager
     */
    protected $criteriaManager;

    /**
     * StepConditionManager constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "criteriaManager" = @DI\Inject("innova_path.manager.criteria")
     * })
     *
     * @param ObjectManager   $om
     * @param CriteriaManager $criteriaManager
     */
    public function __construct(
        ObjectManager $om,
        CriteriaManager $criteriaManager)
    {
        $this->om = $om;
        $this->criteriaManager = $criteriaManager;
    }

    /**
     * Create a new StepCondition from JSON structure.
     *
     * @param Step      $step               Parent path of the condition
     * @param \stdClass $conditionStructure
     *
     * @return StepCondition Edited condition
     */
    public function create(Step $step, \stdClass $conditionStructure)
    {
        return $this->edit($step, new StepCondition(), $conditionStructure);
    }

    /**
     * Update an existing condition from JSON structure.
     *
     * @param Step          $step               Parent step of the condition
     * @param StepCondition $condition          Current condition to edit
     * @param \stdClass     $conditionStructure
     *
     * @return StepCondition Edited condition
     */
    public function edit(Step $step, StepCondition $condition, \stdClass $conditionStructure)
    {
        // Update condition properties
        $condition->setStep($step);

        // Store existing CriteriaGroups to remove those that no longer exist
        $existingGroups = $condition->getCriteriagroups()->toArray();

        $toProcess = !empty($conditionStructure->criteriagroups) ? $conditionStructure->criteriagroups : [];

        // Set up StepCondition criteria
        $createdGroups = $this->updateCriteriaGroups($condition, 0, null, $toProcess);

        // Clean CriteriaGroups to remove
        $this->cleanCriteriaGroups($condition, $createdGroups, $existingGroups);

        // Save modifications
        $this->om->persist($condition);

        return $condition;
    }

    /**
     * Update or create the Criteria of a StepCondition.
     *
     * @param StepCondition $condition The condition to Update
     * @param int           $level
     * @param Criteriagroup $parent
     * @param array         $criteria  The list of criteria of the StepCondition
     *
     * @return array
     */
    public function updateCriteriaGroups(StepCondition $condition, $level = 0, Criteriagroup $parent = null, array $criteria = [])
    {
        $currentOrder = 0;
        $processedGroups = [];

        $existingGroups = $condition->getCriteriagroups();
        foreach ($criteria as $groupStructure) {
            if (empty($group->cgid) || !$existingGroups->containsKey($group->cgid)) {
                // Current CriteriaGroup has never been published or has been deleted => create it
                $criteriaGroup = $this->criteriaManager->createGroup($condition, $level, $parent, $currentOrder, $groupStructure);
            } else {
                // CriteriaGroup already exists => update it
                $criteriaGroup = $existingGroups->get($group->cgid);
                $criteriaGroup = $this->criteriaManager->editGroup($condition, $level, $parent, $currentOrder, $groupStructure, $criteriaGroup);
            }

            // Store CriteriaGroup to know it doesn't have to be deleted when we will clean the StepCondition
            $processedGroups[] = $criteriaGroup;

            // Process children of current group
            if (!empty($groupStructure->criteriagroup)) {
                $childrenLevel = $level + 1;

                $childrenGroups = $this->updateCriteriaGroups($condition, $childrenLevel, $criteriaGroup, $groupStructure->criteriagroup);

                // Store children groups
                $processedGroups = array_merge($processedGroups, $childrenGroups);
            }

            ++$currentOrder;
        }

        return $processedGroups;
    }

    /**
     * Clean Criteria Groups which no longer exist in the current Condition.
     *
     * @param StepCondition $condition
     * @param array         $neededGroups
     * @param array         $existingGroups
     *
     * @return StepConditionManager
     */
    public function cleanCriteriaGroups(StepCondition $condition, array $neededGroups = [], array $existingGroups = [])
    {
        $toRemove = array_filter($existingGroups, function (Criteriagroup $current) use ($neededGroups) {
            $removeGroup = true;
            foreach ($neededGroups as $group) {
                if ($current->getId() === $group->getId()) {
                    $removeGroup = false;
                    break;
                }
            }

            return $removeGroup;
        });

        foreach ($toRemove as $groupToRemove) {
            $condition->removeCriteriagroup($groupToRemove);
            $this->om->remove($groupToRemove);
        }

        return $this;
    }
}
