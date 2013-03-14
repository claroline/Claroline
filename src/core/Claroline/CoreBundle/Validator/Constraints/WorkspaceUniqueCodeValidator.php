<?php

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;

class WorkspaceUniqueCodeValidator extends ConstraintValidator
{
    public function isValid($value, Constraint $constraint)
    {
        $code = trim($value);
        $workspace = $this->em
                        ->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
                        ->findOneBy(array('code' => $code));

        if (!is_null($workspace)) {
            $this->context->addViolation($constraint->message, array('{{ code }}' => $code));
        }

        return true;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
}