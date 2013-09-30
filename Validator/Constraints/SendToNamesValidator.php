<?php

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Validator("send_to_name_validator")
 */
class SendToNamesValidator extends ConstraintValidator
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
        $to = trim($value);

        if (substr($to, -1, 1) === ';') {
            $to = substr_replace($to, "", -1);
        }

        $names = explode(';', $to);
        $usernames = array();
        $groupNames = array();

        foreach ($names as $name) {
            if (substr($name, 0, 1) === '{') {
                $groupNames[] = trim(trim($name), '{}');
            }
            else {
                $usernames[] = trim($name);
            }
        }

        foreach ($usernames as $username) {
            $user = $this->em->getRepository('ClarolineCoreBundle:User')->findOneBy(array('username' => $username));

            if ($user === null) {
                $this->context->addViolation($constraint->message, array('{{ name }}' => $username));
            }
        }

        foreach ($groupNames as $groupName) {
            $group = $this->em->getRepository('ClarolineCoreBundle:Group')->findOneBy(array('name' => $groupName));

            if ($group === null) {
                $this->context->addViolation($constraint->message, array('{{ name }}' => $groupName));
            }
        }
    }
}
