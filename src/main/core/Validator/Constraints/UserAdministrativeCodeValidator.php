<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 1/10/17
 */

namespace Claroline\CoreBundle\Validator\Constraints;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserAdministrativeCodeValidator extends ConstraintValidator
{
    /**
     * @var PlatformConfigurationHandler
     */
    private $platformConfigHandler;
    /**
     * @var ObjectManager
     */
    private $om;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function setServices(
        PlatformConfigurationHandler $platformConfigHandler,
        ObjectManager $om,
        TranslatorInterface $translator
    ) {
        $this->platformConfigHandler = $platformConfigHandler;
        $this->om = $om;
        $this->translator = $translator;
    }

    /**
     * Checks if administration code is unique.
     *
     * @param User $user
     */
    public function validate($user, Constraint $constraint)
    {
        $code = $user->getAdministrativeCode();
        if (!empty($code) && $this->platformConfigHandler->getParameter('is_user_admin_code_unique')) {
            $tmpUser = $this->om->getRepository('ClarolineCoreBundle:User')->findOneByAdministrativeCode($code);
            if ($tmpUser && $tmpUser->getUsername() !== $user->getUsername()) {
                $this->context->addViolationAt(
                    'administrativeCode',
                    $this->translator->trans($constraint->error, ['%code%' => $code], 'platform')
                );
            }
        }
    }
}
