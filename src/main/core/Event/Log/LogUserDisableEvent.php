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

use Claroline\CoreBundle\Entity\User;

class LogUserDisableEvent extends LogGenericEvent
{
    const ACTION = 'security.log.user_disable';

    private $user;

    /**
     * Constructor.
     */
    public function __construct($receiver)
    {
        parent::__construct(
            self::ACTION,
            [
                'receiverUser' => [
                    'lastName' => $receiver->getLastName(),
                    'firstName' => $receiver->getFirstName(),
                ],
            ],
            $receiver
        );

        $this->user = $receiver;
    }

    public static function getRestriction(): array
    {
        return [self::DISPLAYED_ADMIN];
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
