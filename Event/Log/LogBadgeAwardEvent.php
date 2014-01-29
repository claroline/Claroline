<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\Badge\Badge;

class LogBadgeAwardEvent extends LogGenericEvent
{
    const ACTION = 'badge-awarding';

    public function __construct(Badge $badge, $receiver)
    {
        parent::__construct(
            self::ACTION,
            array(
                'badge' => array(
                    'id' => $badge->getId()
                ),
                'receiverUser' => array(
                    'lastName'  => $receiver->getLastName(),
                    'firstName' => $receiver->getFirstName()
                )
            ),
            $receiver
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE, self::DISPLAYED_ADMIN);
    }
}
