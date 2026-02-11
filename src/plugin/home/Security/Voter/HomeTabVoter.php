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
use Claroline\HomeBundle\Manager\HomeRestrictionsManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class HomeTabVoter extends AbstractVoter
{
    public function __construct(
        private readonly HomeRestrictionsManager $homeRestrictionsManager,
    ) {
    }

    public function getClass(): string
    {
        return HomeTab::class;
    }

    /**
     * @param HomeTab $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $isAdmin = $this->isContextToolGranted(self::EDIT, HomeTool::getName(), $object->getContextName(), $object->getContextId());
        if ($isAdmin) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $granted = $this->isContextToolGranted($attributes[0], HomeTool::getName(), $object->getContextName(), $object->getContextId());

        if ($granted) {
            if (self::OPEN !== $attributes[0] || $this->checkTabRestrictions($token, $object)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkTabRestrictions(TokenInterface $token, HomeTab $object): bool
    {
        return empty($this->homeRestrictionsManager->getError($object, $token->getRoleNames()));
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::CREATE, self::EDIT, self::ADMINISTRATE, self::DELETE];
    }
}
