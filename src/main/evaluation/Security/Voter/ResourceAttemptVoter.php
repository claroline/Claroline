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

use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceAttempt;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceAttemptVoter extends AbstractEvaluationVoter
{
    public function getClass(): string
    {
        return ResourceAttempt::class;
    }

    /**
     * @param ResourceAttempt $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        // forward security check to the parent evaluation
        if ($this->isGranted($attributes[0], $object->getResourceUserEvaluation())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
