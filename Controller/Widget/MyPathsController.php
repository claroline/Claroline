<?php

namespace Innova\PathBundle\Controller\Widget;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * MyPathsController
 *
 * @Route(
 *      "/widget",
 *      name    = "innova_path_widget",
 *      service = "innova_path.controller.path_widget"
 * )
 */
class MyPathsController extends Controller
{
    /**
     * Renders all paths for a user
     * @Route(
     *     "/desktop",
     *     name="claro_desktop_paths",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     * @Template("InnovaPathBundle::Widget/listWidget.html.twig")
     */
    public function pathsDesktopWidgetAction()
    {
        $paths = $this->container->get('innova_path.manager.path')->findAccessibleByUser();

        return array (
            'widgetType' => 'desktop',
            'paths'      => $paths,
        );
    }

    /**
     * Renders all paths from a workspace
     * @param Workspace $workspace
     * @return array
     *
     * @Route(
     *     "/workspace",
     *     name="claro_workspace_paths",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     * @ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @Template("InnovaPathBundle::Widget/listWidget.html.twig")
     */
    public function pathsWorkspaceWidgetAction(Workspace $workspace)
    {
        $paths = $this->container->get('innova_path.manager.path')->findAccessibleByUser($workspace);

        return array (
            'widgetType' => 'workspace',
            'workspace'  => $workspace,
            'paths'      => $paths,
        );
    }
}
