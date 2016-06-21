<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Form\WorkspaceImportType;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
 */
class WorkspacesController extends Controller
{
    private $workspaceManager;
    private $om;
    private $eventDispatcher;
    private $workspaceAdminTool;

    /**
     * @DI\InjectParams({
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct(
        WorkspaceManager $workspaceManager,
        ObjectManager $om,
        StrictDispatcher $eventDispatcher
    ) {
        $this->workspaceManager = $workspaceManager;
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @EXT\Route(
     *     "/page/{page}/max/{max}/order/{order}/direction/{direction}/type/{type}",
     *     name="claro_admin_workspaces_management",
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id", "direction"="ASC", "type"=1},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "/page/{page}/search/{search}/max/{max}/order/{order}/direction/{direction}/type/{type}",
     *     name="claro_admin_workspaces_management_search",
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id", "direction"="ASC", "type"=1},
     *     options = {"expose"=true}
     * )
     * @EXT\Template
     *
     * @param $page
     * @param $search
     * @param $max
     * @param $order
     * @param $direction
     * @param $type
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function managementAction($page, $search, $max, $order, $direction, $type = 1)
    {
        $workspaceType = intval($type);

        if ($workspaceType === 2) {
            $pager = $this->workspaceManager->getAllPersonalWorkspaces(
                $page,
                $max,
                $search,
                $order,
                $direction
            );
        } elseif ($workspaceType === 3) {
            $pager = $search === '' ?
                $this->workspaceManager
                    ->findAllWorkspaces($page, $max, $order, $direction) :
                $this->workspaceManager
                    ->getWorkspaceByName($search, $page, $max, $order, $direction);
        } else {
            $pager = $this->workspaceManager->getAllNonPersonalWorkspaces(
                $page,
                $max,
                $search,
                $order,
                $direction
            );
        }

        return array(
            'pager' => $pager,
            'search' => $search,
            'max' => $max,
            'order' => $order,
            'direction' => $direction,
            'type' => $type,
        );
    }

    /**
     * @EXT\Route(
     *     "/visibility",
     *      name="claro_admin_workspaces_management_visibility",
     * )
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleWorkspaceVisibilityAction(Request $request)
    {
        $postData = $request->request->all();
        $workspace = $this->workspaceManager->getWorkspaceById($postData['id']);
        $postData['visible'] === '1' ?
            $workspace->setDisplayable(false) :
            $workspace->setDisplayable(true);
        $this->om->flush();

        return new Response('Visibility changed', 204);
    }

    /**
     * @EXT\Route(
     *     "/registration",
     *      name="claro_admin_workspaces_management_registration",
     * )
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response with the css class to apply to the element
     */
    public function toggleWorkspacePublicRegistrationAction(Request $request)
    {
        $postData = $request->request->all();
        $workspace = $this->workspaceManager->getWorkspaceById($postData['id']);
        $postData['registration'] === 'unlock' ?
            $workspace->setSelfRegistration(false) :
            $workspace->setSelfRegistration(true);
        $this->om->flush();

        return new Response('Registration status changed', 204);
    }

    /**
     * @EXT\Route(
     *     "/",
     *     name="claro_admin_delete_workspaces",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true}
     * )
     *
     * Removes many workspaces from the platform.
     *
     * @param array $workspaces
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteWorkspacesAction(array $workspaces)
    {
        if (count($workspaces) > 0) {
            $this->om->startFlushSuite();

            foreach ($workspaces as $workspace) {
                $this->eventDispatcher->dispatch('log', 'Log\LogWorkspaceDelete', array($workspace));
                $this->workspaceManager->deleteWorkspace($workspace);
            }

            $this->om->endFlushSuite();
        }

        return new Response('Workspace(s) deleted', 204);
    }

    /**
     * @EXT\Route("/import/form", name="claro_admin_workspace_import_form")
     * @EXT\Template
     */
    public function importWorkspaceFormAction()
    {
        $form = $this->createForm(new WorkspaceImportType());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/import", name="claro_admin_workspace_import")
     * @EXT\Template("ClarolineCoreBundle:Administration/Workspaces:importWorkspaceForm.html.twig")
     */
    public function importWorkspaceAction()
    {
        $form = $this->createForm(new WorkspaceImportType());
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $data = file_get_contents($file);
            $data = $this->container->get('claroline.utilities.misc')->formatCsvOutput($data);
            $lines = str_getcsv($data, PHP_EOL);

            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    $workspaces[] = str_getcsv($line, ';');
                }
            }

            $this->workspaceManager->importWorkspaces($workspaces);

            return $this->redirect($this->generateUrl('claro_admin_workspaces_management'));
        }

        return array('form' => $form->createView());
    }
}
