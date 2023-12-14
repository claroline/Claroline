<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

abstract class AbstractEventVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
                $workspace = $object->getWorkspace();
                if ($this->isToolGranted('EDIT', 'agenda', $workspace)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getSupportedActions(): array
    {
        return [self::OPEN, self::CREATE, self::EDIT, self::DELETE];
    }
}
