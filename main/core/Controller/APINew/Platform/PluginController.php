<?php

namespace Claroline\CoreBundle\Controller\APINew\Platform;

use Claroline\AppBundle\Controller\SecurityController;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Manager\PluginManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Manages platform plugins.
 *
 * @EXT\Route("/plugin")
 */
class PluginController extends SecurityController
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
        $this->canOpenAdminTool('main_settings');

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
        $this->canOpenAdminTool('main_settings');
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
        $this->canOpenAdminTool('main_settings');
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
        $this->canOpenAdminTool('main_settings');
        $this->pluginManager->disable($plugin);

        return new JsonResponse(
            $this->pluginManager->getPluginsData()
        );
    }
}
