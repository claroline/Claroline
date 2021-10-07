<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Security\Voter;

use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class QuotaVoter extends AbstractVoter
{
    const MANAGE_QUOTAS = 'MANAGE_QUOTAS';
    const VALIDATE_SUBSCRIPTIONS = 'VALIDATE_SUBSCRIPTIONS';

    public function getClass()
    {
        return Quota::class;
    }

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::MANAGE_QUOTAS:
            case self::VALIDATE_SUBSCRIPTIONS:
                return $this->isToolGranted($attributes[0], 'trainings') ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
