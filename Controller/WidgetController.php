<?php

namespace Innova\PathBundle\Controller;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Innova\PathBundle\Manager\PathManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * WidgetController
 *
 * @Route(
 *      "",
 *      name    = "innova_path_widget",
 *      service = "innova_path.controller.path_widget"
 * )
 */
class WidgetController
{
    /**
     * Path manager
     * @var \Innova\PathBundle\Manager\PathManager
     */
    private $pathManager;

    /**
     * @param \Innova\PathBundle\Manager\PathManager $pathManager
     */
    public function __construct(PathManager $pathManager)
    {
        $this->pathManager = $pathManager;
    }

    /**
     * Renders all paths from a workspace
     * @param Workspace $workspace
     * @return array
     *
     * @Route(
     *     "/path/workspace/{workspaceId}",
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
        $paths = $this->pathManager->findAccessibleByUser($workspace);

        return array (
            'widgetType' => 'workspace',
            'workspace'  => $workspace,
            'paths'      => $paths,
        );
    }

    /**
     * Renders all paths for a user
     * @Route(
     *     "/path/my-paths",
     *     name="my_paths",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     * @Template("InnovaPathBundle::Widget/listWidget.html.twig")
     */
    public function myPathsWidgetAction()
    {
        $paths = $this->pathManager->findAccessibleByUser();

        return array (
            'widgetType' => 'desktop',
            'paths'      => $paths,
        );
    }
}