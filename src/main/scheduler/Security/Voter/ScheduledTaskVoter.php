<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SchedulerBundle\Security\Voter;

use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ScheduledTaskVoter extends AbstractVoter
{
    /**
     * @param ScheduledTask $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        if ($this->hasAdminToolAccess($token, 'scheduled_tasks')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass()
    {
        return ScheduledTask::class;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
