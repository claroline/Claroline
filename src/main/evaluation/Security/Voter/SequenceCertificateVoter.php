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

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\EvaluationBundle\Entity\Certificate\SequenceCertificate;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SequenceCertificateVoter extends AbstractVoter
{
    public function getClass(): string
    {
        return SequenceCertificate::class;
    }

    /**
     * @param SequenceCertificate $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        // forward security check to the parent evaluation
        if ($this->isGranted($attributes[0], $object->getEvaluation())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
