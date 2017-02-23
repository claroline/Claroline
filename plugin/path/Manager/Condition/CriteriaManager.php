<?php

namespace Innova\PathBundle\Manager\Condition;

use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Criteriagroup;
use Innova\PathBundle\Entity\Criterion;
use Innova\PathBundle\Entity\StepCondition;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * CriteriaManager
 * Manages CriteriaGroup and Criterion of a StepCondition.
 *
 * @DI\Service("innova_path.manager.criteria")
 */
class CriteriaManager
{
    /**
     * Object manager.
     *
     * @var ObjectManager
     */
    protected $om;

    /**
     * CriteriaManager constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Create a new Group of Criteria.
     *
     * @param StepCondition $condition
     * @param int           $level
     * @param Criteriagroup $parent
     * @param int           $order
     * @param \stdClass     $groupStructure
     *
     * @return Criteriagroup
     */
    public function createGroup(StepCondition $condition, $level, Criteriagroup $parent = null, $order, \stdClass $groupStructure)
    {
        $group = new Criteriagroup();

        return $this->editGroup($condition, $level, $parent, $order, $groupStructure, $group);
    }

    /**
     * Update an existing group of criteria.
     *
     * @param StepCondition $condition
     * @param int           $level
     * @param Criteriagroup $parent
     * @param int           $order
     * @param \stdClass     $groupStructure
     * @param Criteriagroup $group
     *
     * @return Criteriagroup
     */
    public function editGroup(StepCondition $condition, $level, Criteriagroup $parent = null, $order, \stdClass $groupStructure, Criteriagroup $group)
    {
        // Update group properties
        $group->setCondition($condition);
        $group->setParent($parent);
        $group->setLvl($level);
        $group->setOrder($order);

        // Store existing Criteria to remove those that no longer exist
        $existingCriteria = $group->getCriteria()->toArray();

        $toProcess = !empty($groupStructure->criterion) ? $groupStructure->criterion : [];

        // Manages criteria
        $createdCriteria = $this->updateCriteria($group, $toProcess);

        // Clean CriteriaGroups to remove
        $this->cleanCriteria($group, $createdCriteria, $existingCriteria);

        // Save modifications
        $this->om->persist($group);

        return $group;
    }

    /**
     * Update the Criteria of a CriteriaGroup.
     *
     * @param Criteriagroup $group
     * @param array         $criteria
     *
     * @return array
     */
    public function updateCriteria(Criteriagroup $group, array $criteria = [])
    {
        $processedCriteria = [];

        $existingCriteria = $group->getCriteria();
        foreach ($criteria as $criterionStructure) {
            if (empty($criterionStructure->critid) || !$existingCriteria->containsKey($criterionStructure->critid)) {
                // Current CriteriaGroup has never been published or has been deleted => create it
                $criterion = $this->createCriterion($group, $criterionStructure);
            } else {
                // CriteriaGroup already exists => update it
                $criterion = $existingCriteria->get($group->cgid);
                $criterion = $this->editCriterion($group, $criterionStructure, $criterion);
            }

            // Store CriteriaGroup to know it doesn't have to be deleted when we will clean the StepCondition
            $processedCriteria[] = $criterion;
        }

        return $processedCriteria;
    }

    /**
     * Clean Criteria which no longer exist in the current Group.
     *
     * @param Criteriagroup $group
     * @param array         $neededCriteria
     * @param array         $existingCriteria
     *
     * @return CriteriaManager
     */
    public function cleanCriteria(Criteriagroup $group, array $neededCriteria = [], array $existingCriteria = [])
    {
        $toRemove = array_filter($existingCriteria, function (Criterion $current) use ($neededCriteria) {
            $removeCriterion = true;
            foreach ($neededCriteria as $criterion) {
                if ($current->getId() === $criterion->getId()) {
                    $removeCriterion = false;
                    break;
                }
            }

            return $removeCriterion;
        });

        foreach ($toRemove as $criterionToRemove) {
            $group->removeCriterion($criterionToRemove);
            $this->om->remove($criterionToRemove);
        }

        return $this;
    }

    /**
     * Create a new Criterion.
     *
     * @param Criteriagroup $group
     * @param \stdClass     $criterionStructure
     *
     * @return Criterion
     */
    public function createCriterion(Criteriagroup $group, \stdClass $criterionStructure)
    {
        $criterion = new Criterion();

        return $this->editCriterion($group, $criterionStructure, $criterion);
    }

    /**
     * Update an existing Criterion.
     *
     * @param Criteriagroup $group
     * @param \stdClass     $criterionStructure
     * @param Criterion     $criterion
     *
     * @return Criterion
     */
    public function editCriterion(Criteriagroup $group, \stdClass $criterionStructure, Criterion $criterion)
    {
        // Update criterion properties
        $criterion->setData($criterionStructure->data ? $criterionStructure->data : null);
        $criterion->setCtype($criterionStructure->type ? $criterionStructure->type : null);
        $criterion->setCriteriagroup($group);

        // Save modifications
        $this->om->persist($criterion);

        return $criterion;
    }
}
