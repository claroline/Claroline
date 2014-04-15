<?php


namespace Innova\PathBundle\Controller;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Manager\WorkspaceManager;

/**
 * WidgetController
 */
class WidgetController extends Controller
{

    /**
     * @EXT\Route(
     *     "/path/workspace/{workspaceId}",
     *     name="claro_workspace_paths",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("InnovaPathBundle::Widget/listWidget.html.twig")
     *
     * Renders all paths from a workspace
     *
     * @param AbstractWorkspace $workspace
     */
    public function pathsWorkspaceWidgetAction(AbstractWorkspace $workspace)
    {
        $paths = $this->container->get('innova_path.manager.path')->findAllFromWorkspaceUnsorted($workspace);

        return array('widgetType' => 'workspace', 'workspace' => $workspace, 'paths' => $paths);
    }

    /**
     * @EXT\Route(
     *     "/path/my-paths",
     *     name="my_paths",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("InnovaPathBundle::Widget/listWidget.html.twig")
     *
     * Renders all paths for a user
     *
     */
    public function myPathsWidgetAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $paths = $this->container->get('innova_path.manager.path')->findAllByUser($user);
          
        return array('widgetType' => 'desktop', 'paths' => $paths);
    }
}