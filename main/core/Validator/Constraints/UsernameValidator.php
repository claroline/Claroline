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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @DI\Validator("username_validator")
 */
class UsernameValidator extends ConstraintValidator
{
    private $ch;
    private $om;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "ch"    = @DI\Inject("claroline.config.platform_config_handler"),
     *     "om"    = @DI\Inject("claroline.persistence.object_manager"),
     *     "trans" = @DI\Inject("translator")
     * })
     */
    public function setEntityManager(
        PlatformConfigurationHandler $ch,
        ObjectManager $om,
        TranslatorInterface $translator
    ) {
        $this->ch = $ch;
        $this->om = $om;
        $this->translator = $translator;
    }

    public function validate($user, Constraint $constraint)
    {
        $regex = $this->ch->getParameter('username_regex');
        $username = $user->getUsername();

        if (!preg_match($regex, $username)) {
            $this->context->addViolation($constraint->error);
        }

        if ($username !== $user->getEmail()) {
            $user = $this->om->getRepository('ClarolineCoreBundle:User')->findOneByEmail($username);

            if ($user) {
                $this->context->addViolation($this->translator->trans('username_already_used', ['%username%' => $username], 'platform'));
            }
        }
    }
}
