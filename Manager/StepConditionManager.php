<?php

namespace Innova\PathBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\StepCondition;
use Innova\PathBundle\Entity\Criterion;
use Innova\PathBundle\Entity\Criteriagroup;

class StepConditionManager
{
    /**
     * Object manager
     * @var \Doctrine\Common\Persistence\ObjectManager $om
     */
    protected $om;

    /**
     * Class constructor
     * @param \Doctrine\Common\Persistence\ObjectManager $om
     */
    public function __construct(
        ObjectManager            $om)
    {
        $this->om              = $om;
    }

    /**
     * Create a new stepcondition from JSON structure
     *
     * @param  \Innova\PathBundle\Entity\Step               $step          Parent path of the condition
     * @return \Innova\PathBundle\Entity\StepCondition                     Edited condition
     */
    public function createStepCondition(Step $step)
    {
        $condition = new StepCondition();

        return $this->editStepCondition($step, $condition);
    }

    /**
     * Update an existing condition from JSON structure
     *
     * @param  \Innova\PathBundle\Entity\Step               $step               Parent step of the condition
     * @param  \Innova\PathBundle\Entity\StepCondition      $condition          Current condition to edit
     * @return \Innova\PathBundle\Entity\StepCondition                          Edited condition
     */
    public function editStepCondition(Step $step, StepCondition $condition)
    {
        // Update condition properties
        $condition->setStep($step);

        // Save modifications
        $this->om->persist($condition);

        return $condition;
    }

    /**
     * Create a criteriagroup
     *
     * @param StepCondition $condition
     * @return Criteriagroup
     */
    public function createCriteriagroup($level = 0, $currentOrder = 0, Criteriagroup $parent = null, StepCondition $conditionDB)
    {
        $criteriagroup = new Criteriagroup();

        return $this->editCriteriagroup($level, $currentOrder, $parent, $conditionDB, $criteriagroup);
    }

    /**
     * Edit a criteriagroup
     *
     * @param int $level
     * @param StepCondition $conditionDB
     * @param Criteriagroup $criteriagroupDB
     * @return Criteriagroup
     */
    public function editCriteriagroup($level = 0, $currentOrder = 0, Criteriagroup $parent = null, StepCondition $conditionDB, Criteriagroup $criteriagroupDB)
    {
        // Update criteriagroup properties
        $criteriagroupDB->setStepCondition($conditionDB);
        $criteriagroupDB->setParent($parent);
        $criteriagroupDB->setLvl($level);
        $criteriagroupDB->setOrder($currentOrder);

        // Save modifications
        $this->om->persist($criteriagroupDB);

        return $criteriagroupDB;
    }

    /**
     * Create a criterion
     *
     * @param null $data
     * @param null $ctype
     * @param Criteriagroup $criteriagroup
     * @return Criterion
     */
    public function createCriterion($criteriondata = null, $criteriontype = null, Criteriagroup $criteriagroup)
    {
        $criterion = new Criterion();

        return $this->editCriterion($criteriondata, $criteriontype, $criteriagroup, $criterion);
    }

    /**
     * Edit a criterion
     *
     * @param null|string $criteriondata
     * @param null|string $criteriontype
     * @param Criteriagroup $criteriagroup
     * @param Criterion $criterion
     * @return Criterion
     */
    public function editCriterion($criteriondata = null, $criteriontype = null, Criteriagroup $criteriagroup, Criterion $criterion)
    {
        // Update criterion properties
        $criterion->setData($criteriondata);
        $criterion->setCtype($criteriontype);
        $criterion->setCriteriagroup($criteriagroup);

        // Save modifications
        $this->om->persist($criterion);

        return $criterion;
    }
}
