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

class LogNewPasswordEvent extends LogGenericEvent
{
    const ACTION = 'security.log.new_password';

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
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_ADMIN);
    }
}
