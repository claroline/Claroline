<?php

namespace Claroline\CoreBundle\Controller\APINew\Platform;

use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Manager\PluginManager;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Manages platform plugins.
 *
 * @SEC\PreAuthorize("canOpenAdminTool('main_settings')")
 *
 * @EXT\Route("/plugin")
 */
class PluginController
{
    /** @var PluginManager */
    private $pluginManager;

    /**
     * PluginController constructor.
     *
     * @param PluginManager $pluginManager
     */
    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * @EXT\Route("", name="apiv2_plugin_list")
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        return new JsonResponse(
            $this->pluginManager->getPluginsData()
        );
    }

    /**
     * @EXT\Route("/configure", name="apiv2_plugin_configure")
     * @EXT\Method("PUT")
     *
     * @param Plugin $plugin
     *
     * @return JsonResponse
     */
    public function configureAction(Plugin $plugin)
    {
        // TODO : implement

        return new JsonResponse(
            $this->pluginManager->getPluginsData()
        );
    }

    /**
     * @EXT\Route("/enable", name="apiv2_plugin_enable")
     * @EXT\Method("PUT")
     *
     * @param Plugin $plugin
     *
     * @return JsonResponse
     */
    public function enableAction(Plugin $plugin)
    {
        $this->pluginManager->enable($plugin);

        return new JsonResponse(
            $this->pluginManager->getPluginsData()
        );
    }

    /**
     * @EXT\Route("/disable", name="apiv2_plugin_disable")
     * @EXT\Method("PUT")
     *
     * @param Plugin $plugin
     *
     * @return JsonResponse
     */
    public function disableAction(Plugin $plugin)
    {
        $this->pluginManager->disable($plugin);

        return new JsonResponse(
            $this->pluginManager->getPluginsData()
        );
    }
}
