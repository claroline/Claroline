<?php

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;

class SendToUsernamesValidator extends ConstraintValidator
{
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

        return;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
}


