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
     * @EXT\Template("InnovaPathBundle::Widget/pathsWorkspaceWidget.html.twig")
     *
     * Renders all paths from a workspace
     *
     * @param AbstractWorkspace $workspace
     */
    public function pathsWorkspaceWidgetAction(AbstractWorkspace $workspace)
    {

        $em = $this->getDoctrine()->getManager();
        $paths = $this->container->get('innova_path.manager.path')->findAllFromWorkspace($workspace);

        return array('widgetType' => 'workspace', 'workspace' => $workspace, 'paths' => $paths);
    }
}