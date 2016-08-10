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

class LogHomeTabWorkspaceEditEvent extends LogGenericEvent
{
    const ACTION = 'workspace-home-tab-edit';

    /**
     * Constructor.
     */
    public function __construct(HomeTabConfig $htc)
    {
        $workspace = $htc->getWorkspace();
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
        $details['workspaceId'] = $workspace->getId();
        $details['workspaceCode'] = $workspace->getCode();
        $details['workspaceName'] = $workspace->getName();
        $details['workspaceGuid'] = $workspace->getGuid();

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
