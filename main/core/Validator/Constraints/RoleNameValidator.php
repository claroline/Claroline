<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Translation\TranslatorInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;

/**
 * @DI\Validator("role_name_validator")
 */
class RoleNameValidator extends ConstraintValidator
{
    private $em;
    private $trans;

    /**
     * @DI\InjectParams({
     *      "em"    = @DI\Inject("doctrine.orm.entity_manager"),
     *      "trans" = @DI\Inject("translator"),
     * })
     */
    public function __construct(EntityManager $em, TranslatorInterface $trans)
    {
        $this->em = $em;
        $this->trans = $trans;
    }

    public function validate($value, Constraint $constraint)
    {
        $roleRepo = $this->em->getRepository('ClarolineCoreBundle:Role');

        if ($constraint->wsGuid === null) {
            $roles = $roleRepo->findByName('ROLE_'.$value);
        } else {
            $roles = $roleRepo->findByName('ROLE_WS_'.$value.'_'.$constraint->wsGuid);
        }

        if (trim($value) === '') {
            $this->context->addViolation($this->trans->trans('name_required', array(), 'validators'));
        }

        if (count($roles) >= 1) {
            $this->context->addViolation($constraint->message);
        }
    }
}
