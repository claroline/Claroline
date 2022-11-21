<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\ThemeBundle\Entity\Icon\IconSet;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class IconSetVoter extends AbstractVoter
{
    /**
     * @param IconSet $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::DELETE:
                return $this->hasAdminToolAccess($token, 'main_settings') && !$object->isDefault() ?
                    VoterInterface::ACCESS_GRANTED :
                    VoterInterface::ACCESS_DENIED;
            case self::OPEN:
                return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass(): string
    {
        return IconSet::class;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::CREATE, self::DELETE];
    }
}
