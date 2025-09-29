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

use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceEvaluationVoter extends AbstractEvaluationVoter
{
    public function getClass(): string
    {
        return ResourceEvaluation::class;
    }

    /**
     * @param ResourceEvaluation $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $isAdmin = $this->isToolGranted(self::FOLLOW, 'progression')
            || $this->isToolGranted(self::FOLLOW, 'progression', $object->getResourceNode()->getWorkspace())
            || $this->isGranted(self::FOLLOW, $object->getResourceNode());

        switch ($attributes[0]) {
            case self::OPEN:
                if ($isAdmin) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                if ($token->getUser() instanceof User && $token->getUser()->getId() === $object->getUser()->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

            case self::ADMINISTRATE:
            case self::EDIT:
            case self::DELETE:
                if ($isAdmin) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
