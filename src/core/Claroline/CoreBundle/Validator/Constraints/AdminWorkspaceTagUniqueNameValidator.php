<?php

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Validator("admin_workspace_tag_unique_name_validator")
 */
class AdminWorkspaceTagUniqueNameValidator extends ConstraintValidator
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
}