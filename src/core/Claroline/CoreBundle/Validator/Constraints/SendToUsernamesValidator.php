<?php

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Validator("send_to_username_validator")
 */
class SendToUsernamesValidator extends ConstraintValidator
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
        $to = preg_replace('/\s+/', '', $value);
        if (substr($to, -1, 1) === ';') {
            $to = substr_replace($to, "", -1);
        }
        $usernames = explode(';', $to);
        foreach ($usernames as $username) {
            $user = $this->em->getRepository('ClarolineCoreBundle:User')->findOneBy(array('username' => $username));
            if ($user == null) {
                $this->context->addViolation($constraint->message, array('{{ username }}' => $username));
            }
        }
    }
}


