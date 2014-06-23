<?php


namespace Innova\PathBundle\Controller;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * WidgetController
 */
class WidgetController extends Controller
{

    /**
     * Renders all paths from a workspace
     * @param AbstractWorkspace $workspace
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
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @Template("InnovaPathBundle::Widget/listWidget.html.twig")
     */
    public function pathsWorkspaceWidgetAction(AbstractWorkspace $workspace)
    {
        $paths = $this->container->get('innova_path.manager.path')->findAllFromWorkspaceUnsorted($workspace);

        return array (
            'widgetType' => 'workspace',
            'workspace' => $workspace,
            'paths' => $paths
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
        $user = $this->get('security.context')->getToken()->getUser();
        $paths = $this->container->get('innova_path.manager.path')->findAllByUser($user);
          
        return array (
            'widgetType' => 'desktop',
            'paths' => $paths
        );
    }
}