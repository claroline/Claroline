<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Security\Voter;

use Claroline\CoreBundle\Security\AbstractVoter;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class MessageVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
            case self::PATCH:
                $hasToolAccess = $this->isGranted(['claroline_planned_notification_tool', 'EDIT'], $object->getWorkspace());
                break;
            default:
                $hasToolAccess = $this->isGranted(['claroline_planned_notification_tool', 'OPEN'], $object->getWorkspace());
        }

        return $hasToolAccess ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    public function getClass()
    {
        return 'Claroline\PlannedNotificationBundle\Entity\Message';
    }

    public function getSupportedActions()
    {
        return[self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
