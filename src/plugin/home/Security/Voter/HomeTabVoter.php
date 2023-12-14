<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\HomeBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\HomeBundle\Component\Tool\HomeTool;
use Claroline\HomeBundle\Entity\HomeTab;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class HomeTabVoter extends AbstractVoter
{
    public function getClass(): string
    {
        return HomeTab::class;
    }

    /**
     * @param HomeTab $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $granted = $this->isContextToolGranted($attributes[0], HomeTool::getName(), $object->getContextName(), $object->getContextId());

        if ($granted && $this->checkTabRestrictions($token, $object)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkTabRestrictions(TokenInterface $token, HomeTab $object): bool
    {
        // TODO : this should also check other tab restrictions (dates, code, etc.). Mimic Workspace and Resource

        if (0 === $object->getRoles()->count()) {
            return true;
        }

        foreach ($object->getRoles() as $role) {
            if (in_array($role->getName(), $token->getRoleNames())) {
                return true;
            }
        }

        return false;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::CREATE, self::EDIT, self::ADMINISTRATE, self::DELETE];
    }
}
