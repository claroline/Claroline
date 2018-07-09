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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Repository\Widget\WidgetRepository;
use Claroline\CoreBundle\Repository\WidgetInstanceRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.widget_manager")
 */
class WidgetManager
{
    /** @var ObjectManager */
    private $om;

    /** @var WidgetRepository */
    private $widgetRepository;

    /** @var WidgetInstanceRepository */
    private $widgetInstanceRepository;

    /** @var PluginManager */
    private $pluginManager;

    /**
     * WidgetManager constructor.
     *
     * @DI\InjectParams({
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "pluginManager" = @DI\Inject("claroline.manager.plugin_manager")
     * })
     *
     * @param ObjectManager $om
     * @param PluginManager $pluginManager
     */
    public function __construct(
        ObjectManager $om,
        PluginManager $pluginManager)
    {
        $this->om = $om;
        $this->pluginManager = $pluginManager;
        $this->widgetRepository = $om->getRepository('ClarolineCoreBundle:Widget\Widget');
        $this->widgetInstanceRepository = $om->getRepository('ClarolineCoreBundle:Widget\WidgetInstance');
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
        $enabledPlugins = $this->pluginManager->getEnabled(true);

        return $this->widgetRepository->findAllAvailable($enabledPlugins, $context);
    }

    /**
     * @param null $organizations
     *
     * @return int
     */
    public function getNbWidgetInstances($organizations = null)
    {
        return $this->widgetInstanceRepository->countWidgetInstances(null, $organizations);
    }

    /**
     * @param null $organizations
     *
     * @return int
     */
    public function getNbWorkspaceWidgetInstances($organizations = null)
    {
        return $this->widgetInstanceRepository->countWidgetInstances('workspace', $organizations);
    }

    /**
     * @param null $organizations
     *
     * @return int
     */
    public function getNbDesktopWidgetInstances($organizations = null)
    {
        return $this->widgetInstanceRepository->countWidgetInstances('desktop', $organizations);
    }

    /**
     * @param null $organizations
     *
     * @return array
     */
    public function countWidgetsByType($organizations = null)
    {
        return $this->widgetInstanceRepository->countByType($organizations);
    }
}
