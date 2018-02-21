<?php

namespace HeVinci\CompetencyBundle\Validator;

use Claroline\AppBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Competency;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @DI\Validator("competency_name_validator")
 *
 * Validator ensuring that a competency has a unique name
 * within a competency framework.
 */
class UniqueCompetencyValidator extends ConstraintValidator
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

    public function validate($competency, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueCompetency) {
            throw new \InvalidArgumentException(sprintf(
                'Expected UniqueCompetency constraint, got %s',
                get_class($constraint)
            ));
        }

        if (!$competency instanceof Competency) {
            throw new UnexpectedTypeException($competency, 'Competency');
        }

        $parent = $constraint->parentCompetency ?: $competency->getParent();

        if (!$parent || !$parent instanceof Competency) {
            throw new \LogicException(
                'Cannot validate competency name without a parent competency reference '
                .'the competency hasn\'t a parent yet and no parent was provided '
                .'in the constraint (did you forget to pass a reference through the '
                .'"parent_competency" form option ?)'
            );
        }

        $duplicate = $this->om->getRepository('HeVinciCompetencyBundle:Competency')
            ->findOneBy([
                'name' => $competency->getName(),
                'root' => $parent->getRoot(),
            ]);

        if ($duplicate) {
            $this->context->addViolationAt('name', $constraint->message);
        }
    }
}
