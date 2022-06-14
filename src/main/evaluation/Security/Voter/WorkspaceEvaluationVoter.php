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
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class WorkspaceEvaluationVoter extends AbstractEvaluationVoter
{
    public function getClass(): string
    {
        return Evaluation::class;
    }

    /**
     * @param Evaluation $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        $isAdmin = $this->isToolGranted(self::EDIT, 'evaluation')
            || $this->isToolGranted(self::EDIT, 'evaluation', $object->getWorkspace());

        switch ($attributes[0]) {
            case self::OPEN:
            case self::VIEW:
                if ($isAdmin) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                if ($token->getUser() instanceof User && $token->getUser()->getId() === $object->getUser()->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                $canShowEval = $this->isToolGranted(self::SHOW_EVALUATIONS, 'evaluation')
                    || $this->isToolGranted(self::EDIT, 'evaluation', $object->getWorkspace());
                if ($canShowEval) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                return VoterInterface::ACCESS_DENIED;

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
