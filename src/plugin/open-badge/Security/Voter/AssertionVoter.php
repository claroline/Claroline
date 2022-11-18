<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AssertionVoter extends AbstractVoter
{
    public function getClass(): string
    {
        return Assertion::class;
    }

    /**
     * @param Assertion $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $currentUser = null;
        if ($token->getUser() instanceof User) {
            $currentUser = $token->getUser();
        }

        switch ($attributes[0]) {
            case self::OPEN:
                // has grant rights on the badge or is owner
                if ($this->isGranted('GRANT', $object->getBadge())
                    || (!empty($currentUser) && !empty($object->getRecipient()) && $currentUser->getId() === $object->getRecipient()->getId())) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::CREATE:
            case self::EDIT:
            case self::ADMINISTRATE:
            case self::DELETE:
                // has grant rights on the badge
                if ($this->isGranted('GRANT', $object->getBadge())) {
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
