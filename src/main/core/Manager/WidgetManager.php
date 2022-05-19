<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Repository\Widget\WidgetRepository;

class WidgetManager
{
    /** @var ObjectManager */
    private $om;

    /** @var WidgetRepository */
    private $widgetRepository;

    /** @var PluginManager */
    private $pluginManager;

    public function __construct(
        ObjectManager $om,
        PluginManager $pluginManager
    ) {
        $this->om = $om;
        $this->pluginManager = $pluginManager;
        $this->widgetRepository = $om->getRepository(Widget::class);
    }

    /**
     * Get the list of available widgets in the platform.
     *
     * @param string $context
     *
     * @return array
     */
    public function getAvailable($context = null)
    {
        $enabledPlugins = $this->pluginManager->getEnabled();

        return $this->widgetRepository->findAllAvailable($enabledPlugins, $context);
    }
}
