<?php

namespace Claroline\CoreBundle\Controller\APINew\Platform;

use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Manager\PluginManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages platform plugins.
 *
 * @Route("/plugin")
 */
class PluginController extends AbstractSecurityController
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
     * @Route("", name="apiv2_plugin_list")
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
     * @Route("/configure", name="apiv2_plugin_configure", methods={"PUT"})
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
     * @Route("/enable", name="apiv2_plugin_enable", methods={"PUT"})
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
     * @Route("/disable", name="apiv2_plugin_disable", methods={"PUT"})
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
