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

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;

class LogWidgetAdminHideEvent extends LogGenericEvent
{
    const ACTION = 'admin-widget-hide';

    /**
     * Constructor.
     */
    public function __construct(HomeTab $homeTab, WidgetHomeTabConfig $whtc, WidgetDisplayConfig $wdc = null)
    {
        $widgetInstance = $whtc->getWidgetInstance();
        $widget = $widgetInstance->getWidget();
        $details = [];
        $details['tabId'] = $homeTab->getId();
        $details['tabName'] = $homeTab->getName();
        $details['tabType'] = $homeTab->getType();
        $details['tabIcon'] = $homeTab->getIcon();
        $details['widgetId'] = $widget->getId();
        $details['widgetName'] = $widget->getName();
        $details['widgetIsConfigurable'] = $widget->isConfigurable();
        $details['widgetIsExportable'] = $widget->isExportable();
        $details['widgetIsDisplayableInWorkspace'] = $widget->isDisplayableInWorkspace();
        $details['widgetIsDisplayableInDesktop'] = $widget->isDisplayableInDesktop();
        $details['id'] = $widgetInstance->getId();
        $details['name'] = $widgetInstance->getName();
        $details['icon'] = $widgetInstance->getIcon();
        $details['isAdmin'] = $widgetInstance->isAdmin();
        $details['isDesktop'] = $widgetInstance->isDesktop();
        $details['widgetHomeTabConfigId'] = $whtc->getId();
        $details['order'] = $whtc->getWidgetOrder();
        $details['type'] = $whtc->getType();
        $details['visible'] = $whtc->isVisible();
        $details['locked'] = $whtc->isLocked();

        if (!is_null($wdc)) {
            $details['widgetDisplayConfigId'] = $wdc->getId();
            $details['row'] = $wdc->getRow();
            $details['column'] = $wdc->getColumn();
            $details['width'] = $wdc->getWidth();
            $details['height'] = $wdc->getHeight();
            $details['color'] = $wdc->getColor();
        }

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
