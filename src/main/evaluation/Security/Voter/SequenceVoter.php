<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Security\Voter;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SequenceVoter extends AbstractVoter
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function getClass(): string
    {
        return Sequence::class;
    }

    /**
     * @param Sequence $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $isAdmin = $this->isToolGranted(self::EDIT, 'progression', $object->getWorkspace());
        if ($isAdmin) {
            return VoterInterface::ACCESS_GRANTED;
        }

        switch ($attributes[0]) {
            case self::OPEN:
                if ($this->isToolGranted(self::OPEN, 'progression', $object->getWorkspace())) {
                    if ($object->isPublic()
                        || ($token?->getUser() && $this->om->getRepository(Sequence::class)->isAssigned($object, $token->getUser()))
                    ) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }

                return VoterInterface::ACCESS_DENIED;

            case self::FOLLOW:
                if ($this->isToolGranted(self::FOLLOW, 'progression', $object->getWorkspace())) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::CREATE:
            case self::ADMINISTRATE:
            case self::EDIT:
            case self::DELETE:
                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getSupportedActions(): ?array
    {
        return [self::OPEN, self::FOLLOW, self::CREATE, self::EDIT, self::DELETE, self::ADMINISTRATE];
    }
}
