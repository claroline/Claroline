<?php

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Validator("workspace_unique_code_validator")
 */
class WorkspaceUniqueCodeValidator extends ConstraintValidator
{
    private $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        $code = trim($value);
        $workspace = $this->em
                        ->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
                        ->findOneBy(array('code' => $code));

        if ($workspace) {
            $this->context->addViolation($constraint->message, array('{{ code }}' => $code));
        }
    }
}
