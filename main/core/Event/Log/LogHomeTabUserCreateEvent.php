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

use Claroline\CoreBundle\Entity\Home\HomeTabConfig;

class LogHomeTabUserCreateEvent extends LogGenericEvent
{
    const ACTION = 'user-home-tab-create';

    /**
     * Constructor.
     */
    public function __construct(HomeTabConfig $htc)
    {
        $homeTab = $htc->getHomeTab();
        $details = [];
        $details['tabId'] = $homeTab->getId();
        $details['tabName'] = $homeTab->getName();
        $details['tabType'] = $homeTab->getType();
        $details['tabIcon'] = $homeTab->getIcon();
        $details['configId'] = $htc->getId();
        $details['type'] = $htc->getType();
        $details['locked'] = $htc->isLocked();
        $details['visible'] = $htc->isVisible();
        $details['tabOrder'] = $htc->getTabOrder();
        $details['details'] = $htc->getDetails();

        parent::__construct(
            self::ACTION,
            $details
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::PLATFORM_EVENT_TYPE];
    }
}
