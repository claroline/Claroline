<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\RoutingHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Used to declare redirection to maintain some old routes for retro-compatibility.
 * Routes are not named to avoid someone using it directly.
 */
class LegacyController
{
    /** @var ObjectManager */
    private $om;
    /** @var RoutingHelper */
    private $routingHelper;

    /**
     * LegacyController constructor.
     *
     * @param ObjectManager $om
     * @param RoutingHelper $routingHelper
     */
    public function __construct(ObjectManager $om, RoutingHelper $routingHelper)
    {
        $this->om = $om;
        $this->routingHelper = $routingHelper;
    }

    /**
     * Pre SPA resource route.
     *
     * @EXT\Route("/resources/show/{type}/{id}")
     * @EXT\Route("/resources/show/{id}")
     * @EXT\Route("/workspaces/{workspaceId}/open/tool/resource_manager/{id}")
     * @EXT\Method("GET")
     *
     * @param string $id
     *
     * @return RedirectResponse
     */
    public function openResourceAction($id)
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $this->om->find(ResourceNode::class, $id);

        if ($resourceNode) {
            return new RedirectResponse(
                $this->routingHelper->resourcePath($resourceNode)
            );
        }

        return new RedirectResponse(
            // go to resource path even if it does not exist to have correct 404 page
            $this->routingHelper->resourcePath($id)
        );
    }

    /**
     * Pre SPA workspace route.
     *
     * @EXT\Route("/workspaces/{id}/open/tool/{toolName}")
     * @EXT\Route("/workspaces/{id}/open")
     * @EXT\Method("GET")
     *
     * @param string $id
     * @param string $toolName
     *
     * @return RedirectResponse
     */
    public function openWorkspaceAction($id, $toolName = null)
    {
        /** @var Workspace $workspace */
        $workspace = $this->om->find(Workspace::class, $id);

        if ($workspace) {
            return new RedirectResponse(
                $this->routingHelper->workspacePath($workspace, $toolName)
            );
        }

        return new RedirectResponse(
            // go to workspace path even if it does not exist to have correct 404 page
            $this->routingHelper->workspacePath($id)
        );
    }

    /**
     * Pre SPA main home route.
     *
     * @EXT\Route("/apiv2/")
     * @EXT\Route("/apiv2")
     * @EXT\Method("GET")
     *
     * @return RedirectResponse
     */
    public function openPlatformHomeAction()
    {
        return new RedirectResponse(
            // go to platform main home tab
            $this->routingHelper->indexPath().'#/home'
        );
    }

    /**
     * Pre SPA desktop home route.
     *
     * @EXT\Route("/desktop/tool/open/home")
     * @EXT\Method("GET")
     *
     * @return RedirectResponse
     */
    public function openDesktopHomeAction()
    {
        return new RedirectResponse(
            // go to desktop main home tab
            $this->routingHelper->desktopPath('home')
        );
    }

    /**
     * Pre SPA desktop reset password route.
     *
     * @EXT\Route("/login")
     * @EXT\Method("GET")
     *
     * @return RedirectResponse
     */
    public function loginAction()
    {
        return new RedirectResponse(
            $this->routingHelper->indexPath().'#/login'
        );
    }

    /**
     * Pre SPA reset password route.
     *
     * @EXT\Route("/reset")
     * @EXT\Method("GET")
     *
     * @return RedirectResponse
     */
    public function resetPasswordAction()
    {
        return new RedirectResponse(
        // go to desktop main home tab
            $this->routingHelper->indexPath().'#/reset_password'
        );
    }
}
