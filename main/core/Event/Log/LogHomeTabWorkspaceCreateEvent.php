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

use Claroline\CoreBundle\Entity\Tab\HomeTabConfig;

class LogHomeTabWorkspaceCreateEvent extends LogGenericEvent
{
    const ACTION = 'workspace-home-tab-create';

    /**
     * Constructor.
     */
    public function __construct(HomeTabConfig $htc)
    {
        $homeTab = $htc->getHomeTab();
        $workspace = $homeTab->getWorkspace();
        $details = [];
        $details['tabId'] = $homeTab->getId();
        $details['tabName'] = $htc->getName();
        $details['tabType'] = $homeTab->getType();
        $details['tabIcon'] = $htc->getIcon();
        $details['configId'] = $htc->getId();
        $details['type'] = $homeTab->getType();
        $details['visible'] = $htc->isVisible();
        $details['tabOrder'] = $htc->getTabOrder();
        $details['details'] = $htc->getDetails();
        $details['workspaceId'] = $workspace->getId();
        $details['workspaceCode'] = $workspace->getCode();
        $details['workspaceName'] = $workspace->getName();
        $details['workspaceGuid'] = $workspace->getUuid();

        parent::__construct(
            self::ACTION,
            $details,
            null,
            null,
            null,
            null,
            $workspace
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
    }
}
