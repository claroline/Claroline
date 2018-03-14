<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter;

use Claroline\CoreBundle\Security\AbstractVoter;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class AdministrationToolVoter extends AbstractVoter implements VoterInterface
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        $roles = $object->getRoles();
        $tokenRoles = $token->getRoles();

        foreach ($tokenRoles as $tokenRole) {
            foreach ($roles as $role) {
                if ($role->getRole() === $tokenRole->getRole()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Tool\AdminTool';
    }

    public function getSupportedActions()
    {
        //atm, null means "everything is supported... implement this later"
        return null;
    }
}
