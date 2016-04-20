<?php

namespace Innova\PathBundle\Manager\Condition;

use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\StepCondition;
use Innova\PathBundle\Entity\Criteriagroup;

/**
 * StepConditionManager
 * Manages access conditions to the Steps of a Path.
 */
class StepConditionManager
{
    /**
     * Object manager.
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * Criteria Manager.
     *
     * @var \Innova\PathBundle\Manager\Condition\CriteriaManager
     */
    protected $criteriaManager;

    /**
     * Class constructor.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager           $om
     * @param \Innova\PathBundle\Manager\Condition\CriteriaManager $criteriaManager
     */
    public function __construct(ObjectManager $om, CriteriaManager $criteriaManager)
    {
        $this->om = $om;
        $this->criteriaManager = $criteriaManager;
    }

    /**
     * Create a new StepCondition from JSON structure.
     *
     * @param \Innova\PathBundle\Entity\Step $step               Parent path of the condition
     * @param \stdClass                      $conditionStructure
     *
     * @return \Innova\PathBundle\Entity\StepCondition Edited condition
     */
    public function create(Step $step, \stdClass $conditionStructure)
    {
        return $this->edit($step, new StepCondition(), $conditionStructure);
    }

    /**
     * Update an existing condition from JSON structure.
     *
     * @param \Innova\PathBundle\Entity\Step          $step               Parent step of the condition
     * @param \Innova\PathBundle\Entity\StepCondition $condition          Current condition to edit
     * @param \stdClass                               $conditionStructure
     *
     * @return \Innova\PathBundle\Entity\StepCondition Edited condition
     */
    public function edit(Step $step, StepCondition $condition, \stdClass $conditionStructure)
    {
        // Update condition properties
        $condition->setStep($step);

        // Store existing CriteriaGroups to remove those that no longer exist
        $existingGroups = $condition->getCriteriagroups()->toArray();

        $toProcess = !empty($conditionStructure->criteriagroups) ? $conditionStructure->criteriagroups : array();

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
     * @param array         $criteria  The list of criteria of the StepCondition
     *
     * @return array
     */
    public function updateCriteriaGroups(StepCondition $condition, $level = 0, Criteriagroup $parent = null, array $criteria = array())
    {
        $currentOrder = 0;
        $processedGroups = array();

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
     * Clean Criteria Gorups which no longer exist in the current Condition.
     *
     * @param \Innova\PathBundle\Entity\StepCondition $condition
     * @param array                                   $neededGroups
     * @param array                                   $existingGoups
     *
     * @return \Innova\PathBundle\Manager\Condition\StepConditionManager
     */
    public function cleanCriteriaGroups(StepCondition $condition, array $neededGroups = array(), array $existingGoups = array())
    {
        $toRemove = array_filter($existingGoups, function (Criteriagroup $current) use ($neededGroups) {
            $removeGroup = true;
            foreach ($neededGroups as $group) {
                if ($current->getId() == $group->getId()) {
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
