<?php

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;

class AdminWorkspaceTagUniqueNameValidator extends ConstraintValidator
{
    public function isValid($value, Constraint $constraint)
    {
        $name = trim($value);
        $workspaceTag = $this->em
            ->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findOneBy(array('user' => null, 'name' => $name));

        if (!is_null($workspaceTag)) {
            $this->context->addViolation($constraint->message, array('{{ name }}' => $name));
        }

        return true;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
}