<?php

namespace Claroline\EvaluationBundle\Security\Voter;

use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\UserEvaluation\WorkspaceEvaluation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class WorkspaceEvaluationVoter extends AbstractEvaluationVoter
{
    public function getClass(): string
    {
        return WorkspaceEvaluation::class;
    }

    /**
     * @param WorkspaceEvaluation $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        $isAdmin = $this->isToolGranted(self::FOLLOW, 'progression')
            || $this->isToolGranted(self::FOLLOW, 'progression', $object->getWorkspace());

        switch ($attributes[0]) {
            case self::OPEN:
                if ($isAdmin) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                if ($token->getUser() instanceof User && $token->getUser()?->getId() === $object->getUser()?->getId()) {
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
