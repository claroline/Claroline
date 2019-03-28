<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This controller is used to do some redirects/route alias. It's not always possible to do it
 * in the concerned controller because path do have prefixes we want to remove/override sometimes.
 *
 * @EXT\Route("/", options={"expose"=true})
 */
class RedirectController extends Controller
{
    /**
     * Renders a resource application. Used for old links compatibility.
     *
     * @EXT\Route("/resource/open/{node}", name="claro_resource_open_short")
     * @EXT\Route("/resource/open/{resourceType}/{node}", name="claro_resource_open")
     * @EXT\Method("GET")
     *
     * @param string|int $node
     *
     * @return RedirectResponse
     */
    public function openResourceAction($node)
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $this->container->get('claroline.persistence.object_manager')->find(ResourceNode::class, $node);

        return $this->redirectToRoute('claro_resource_show', [
            'id' => $resourceNode->getUuid(),
            'type' => $resourceNode->getResourceType()->getId(),
        ]);
    }

    /**
     * @EXT\Route("ws/{slug}/")
     * @EXT\Route("ws/{slug}")
     * @EXT\ParamConverter("workspace",  options={"mapping": {"slug": "slug"}})
     *
     * @param Workspace $workspace
     *
     * @return RedirectResponse
     */
    public function openWorkspaceSlugAction(Workspace $workspace)
    {
        return $this->redirectToRoute('claro_workspace_open', [
            'workspaceId' => $workspace->getId(),
        ]);
    }

    /**
     * @EXT\Route("ws/{slug}/subscription", name="claro_workspace_subscription_url_generate")
     * @EXT\ParamConverter("workspace", options={"mapping": {"slug": "slug"}})
     *
     * @param Workspace $workspace
     *
     * @return RedirectResponse
     */
    public function urlSubscriptionGenerateAction(Workspace $workspace, Request $request)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        if ('anon.' === $user) {
            $configHandler = $this->container->get('claroline.config.platform_config_handler');

            if (!$configHandler->getParameter('registration.self')) {
                throw new AccessDeniedException();
            }

            return $this->redirectToRoute('claro_workspace_subscription_url_generate_anonymous', [
                'workspace' => $workspace->getId(),
                '_code' => $request->query->get('_code'),
            ]);
        } else {
            return $this->redirectToRoute('claro_workspace_subscription_url_generate_user', [
                'workspace' => $workspace->getId(),
                '_code' => $request->query->get('_code'),
            ]);
        }
    }
}
