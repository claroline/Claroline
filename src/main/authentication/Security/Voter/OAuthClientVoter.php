<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\AuthenticationBundle\Entity\OAuthClient;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class OAuthClientVoter extends AbstractVoter
{
    /**
     * @param OAuthClient $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        if ($this->isGranted(PlatformRoles::ADMIN)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass(): string
    {
        return OAuthClient::class;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::CREATE, self::EDIT, self::ADMINISTRATE, self::DELETE];
    }
}
