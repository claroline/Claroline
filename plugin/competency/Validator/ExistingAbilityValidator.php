<?php

namespace HeVinci\CompetencyBundle\Validator;

use Claroline\AppBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Ability;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @DI\Validator("existing_ability_validator")
 *
 * Validator ensuring that an ability name matches an existing ability.
 */
class ExistingAbilityValidator extends ConstraintValidator
{
    private $om;

    /**
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

    public function validate($value, Constraint $constraint)
    {
        if ($value && !$value instanceof Ability) {
            $ability = $this->om->getRepository('HeVinciCompetencyBundle:Ability')
                ->findOneBy(['name' => $value]);

            if (!$ability) {
                $this->context->addViolation($constraint->message);
            }
        }
    }
}
