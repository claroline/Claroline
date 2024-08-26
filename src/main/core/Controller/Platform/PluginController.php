<?php

namespace Claroline\CoreBundle\Controller\Platform;

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
 * @Route("/plugin", name="apiv2_plugin_")
 */
class PluginController extends AbstractSecurityController
{
    public function __construct(
        private readonly Crud $crud,
        private readonly SerializerProvider $serializer,
        private readonly PluginManager $pluginManager
    ) {
    }

    /**
     * @Route("", name="list", methods={"GET"})
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('parameters');

        return new JsonResponse(
            $this->crud->list(Plugin::class, $request->query->all(), [Options::SERIALIZE_LIST])
        );
    }

    /**
     * @Route("/{id}", name="get", methods={"GET"})
     */
    public function getAction(Plugin $plugin): JsonResponse
    {
        $this->canOpenAdminTool('parameters');

        return new JsonResponse(
            $this->serializer->serialize($plugin)
        );
    }

    /**
     * @Route("/{id}/configure", name="configure", methods={"PUT"})
     */
    public function configureAction(Plugin $plugin): JsonResponse
    {
        $this->canOpenAdminTool('parameters');

        return new JsonResponse(
            $this->serializer->serialize($plugin)
        );
    }

    /**
     * @Route("/{id}/enable", name="enable", methods={"PUT"})
     */
    public function enableAction(Plugin $plugin): JsonResponse
    {
        $this->canOpenAdminTool('parameters');

        $this->pluginManager->enable($plugin);

        return new JsonResponse(
            $this->serializer->serialize($plugin)
        );
    }

    /**
     * @Route("/{id}/disable", name="disable", methods={"PUT"})
     */
    public function disableAction(Plugin $plugin): JsonResponse
    {
        $this->canOpenAdminTool('parameters');

        $this->pluginManager->disable($plugin);

        return new JsonResponse(
            $this->serializer->serialize($plugin)
        );
    }
}
