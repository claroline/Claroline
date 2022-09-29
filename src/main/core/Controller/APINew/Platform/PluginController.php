<?php

namespace Claroline\CoreBundle\Controller\APINew\Platform;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Manager\PluginManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages platform plugins.
 *
 * @Route("/plugin")
 */
class PluginController extends AbstractSecurityController
{
    /** @var Crud */
    private $crud;
    /** @var SerializerProvider */
    private $serializer;
    /** @var PluginManager */
    private $pluginManager;

    public function __construct(
        Crud $crud,
        SerializerProvider $serializer,
        PluginManager $pluginManager
    ) {
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->pluginManager = $pluginManager;
    }

    /**
     * @Route("", name="apiv2_plugin_list")
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('plugins');

        return new JsonResponse(
            $this->crud->list(Plugin::class, $request->query->all(), [Options::SERIALIZE_LIST])
        );
    }

    /**
     * @Route("/{id}", name="apiv2_plugin_get")
     */
    public function getAction(Plugin $plugin): JsonResponse
    {
        $this->canOpenAdminTool('plugins');

        return new JsonResponse(
            $this->serializer->serialize($plugin)
        );
    }

    /**
     * @Route("/{id}/configure", name="apiv2_plugin_configure", methods={"PUT"})
     */
    public function configureAction(Plugin $plugin): JsonResponse
    {
        $this->canOpenAdminTool('plugins');

        return new JsonResponse(
            $this->serializer->serialize($plugin)
        );
    }

    /**
     * @Route("/{id}/enable", name="apiv2_plugin_enable", methods={"PUT"})
     */
    public function enableAction(Plugin $plugin): JsonResponse
    {
        $this->canOpenAdminTool('plugins');

        $this->pluginManager->enable($plugin);

        return new JsonResponse(
            $this->serializer->serialize($plugin)
        );
    }

    /**
     * @Route("/{id}/disable", name="apiv2_plugin_disable", methods={"PUT"})
     */
    public function disableAction(Plugin $plugin): JsonResponse
    {
        $this->canOpenAdminTool('plugins');

        $this->pluginManager->disable($plugin);

        return new JsonResponse(
            $this->serializer->serialize($plugin)
        );
    }
}
