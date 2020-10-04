<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter\Tool;

use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AdministrationToolVoter extends AbstractVoter implements VoterInterface
{
    /**
     * @param AdminTool $object
     *
     * @return int
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        if ($this->isAdmin($token)) {
            return VoterInterface::ACCESS_GRANTED;
        }

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
        return AdminTool::class;
    }

    public function getSupportedActions()
    {
        //atm, null means "everything is supported... implement this later"
        return null;
    }
}
