<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BookingBundle\Event\Log;

use Claroline\BookingBundle\Entity\Material;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;

class LogMaterialDeleteEvent extends LogGenericEvent
{
    const ACTION = 'bookingbundle-material-delete';

    public function __construct(Material $material)
    {
        $details = [];
        $details['id'] = $material->getUuid();
        $details['title'] = $material->getName();
        $details['code'] = $material->getCode();
        $details['quantity'] = $material->getQuantity();

        parent::__construct(self::ACTION, $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_ADMIN];
    }
}
