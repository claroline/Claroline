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
use Claroline\AuthenticationBundle\Entity\ApiToken;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ApiTokenVoter extends AbstractVoter
{
    /**
     * @param ApiToken $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        if ($token->getUser() instanceof User) {
            switch ($attributes[0]) {
                case self::EDIT:
                case self::DELETE:
                    if ($object->isLocked()) {
                        return VoterInterface::ACCESS_DENIED;
                    }
                    // no break
                case self::CREATE:
                case self::VIEW:
                    $isAdmin = $this->hasAdminToolAccess($token, 'integration');
                    if ($isAdmin || (!empty($object->getUser()) && $object->getUser()->getUuid() === $token->getUser()->getUuid())) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass(): string
    {
        return ApiToken::class;
    }

    public function getSupportedActions(): array
    {
        return [self::VIEW, self::CREATE, self::EDIT, self::DELETE];
    }
}
