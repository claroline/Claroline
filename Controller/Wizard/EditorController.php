<?php

namespace Innova\PathBundle\Controller\Wizard;

use Innova\PathBundle\Manager\PathManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Innova\PathBundle\Entity\Path\Path;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\ResourceManager;

/**
 * Class EditorController
 *
 * @Route(
 *      "workspaces/{workspaceId}/tool/path",
 *      name    = "innova_path_editor",
 *      service = "innova_path.controller.path_editor"
 * )
 * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\Workspace", options={"mapping": {"workspaceId": "id"}})
 */
class EditorController
{
    /**
     * Object manager
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     *
     * Router
     * @var \Symfony\Component\Routing\RouterInterface $router
     */
    protected $router;

    /**
     * Path manager
     * @var \Innova\PathBundle\Manager\PathManager
     */
    protected $pathManager;

    /**
     * Resource manager
     * @var \Claroline\CoreBundle\Manager\ResourceManager
     */
    protected $resourceManager;

    /**
     * Class constructor
     * @param \Doctrine\Common\Persistence\ObjectManager                 $objectManager
     * @param \Symfony\Component\Routing\RouterInterface                 $router
     * @param \Innova\PathBundle\Manager\PathManager                     $pathManager
     * @param \Claroline\CoreBundle\Manager\ResourceManager              $resourceManager
     */
    public function __construct(
        ObjectManager        $objectManager,
        RouterInterface      $router,
        PathManager          $pathManager,
        ResourceManager      $resourceManager)
    {
        $this->om                = $objectManager;
        $this->router            = $router;
        $this->pathManager       = $pathManager;
        $this->resourceManager   = $resourceManager;
    }

    /**
     * Display Path Editor
     * @Route(
     *      "/editor",
     *      name    = "innova_path_editor_wizard",
     *      options = {"expose" = true}
     * )
     * @Method("GET")
     * @Template("InnovaPathBundle:Wizard:editor.html.twig")
     */
    public function displayAction(Workspace $workspace, Path $path = null)
    {
        // Check User credentials

        if (empty($path)) {
            $path = new Path();
        }

        // Get workspace root directory
        $wsDirectory = $this->resourceManager->getWorkspaceRoot($workspace);
        $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $resourceIcons = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findByIsShortcut(false);

        return array (
            '_resource'     => $path,
            'workspace'     => $workspace,
            'wsDirectoryId' => $wsDirectory->getId(),
            'resourceTypes' => $resourceTypes,
            'resourceIcons' => $resourceIcons,
        );
    }

    /**
     * Create a new path
     * @Route(
     *      "/new",
     *      name    = "innova_path_editor_new",
     *      options = {"expose" = true}
     * )
     * @Method({"GET", "POST"})
     * @Template("InnovaPathBundle:Editor:main.html.twig")
     */
    public function newAction(Workspace $workspace)
    {
        $path = Path::initialize();
        $this->pathManager->checkAccess('CREATE', $path, $workspace);

        return $this->renderEditor($workspace, $path);
    }

    /**
     * Edit an existing path
     * @Route(
     *      "/edit/{id}",
     *      name         = "innova_path_editor_edit",
     *      requirements = {"id" = "\d+"},
     *      options      = {"expose" = true}
     * )
     * @Method({"GET", "PUT"})
     * @Template("InnovaPathBundle:Editor:main.html.twig")
     */
    public function editAction(Workspace $workspace, Path $path)
    {
        $this->pathManager->checkAccess('EDIT', $path);

        return $this->renderEditor($workspace, $path);
    }

    /**
     * Render Editor UI
     * @param  \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param  \Innova\PathBundle\Entity\Path\Path              $path
     * @return array
     */
    protected function renderEditor(Workspace $workspace, Path $path)
    {
        // Get workspace root directory
        $wsDirectory = $this->resourceManager->getWorkspaceRoot($workspace);
        $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $resourceIcons = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findByIsShortcut(false);

        return array (
            '_resource'     => $path,
            'workspace'     => $workspace,
            'wsDirectoryId' => $wsDirectory->getId(),
            'resourceTypes' => $resourceTypes,
            'resourceIcons' => $resourceIcons,
        );
    }

    /**
     * Load activity data from ResourceNode id
     * @param  integer $nodeId
     * @return JsonResponse
     *
     * @Route(
     *      "/load_activity/{nodeId}",
     *      name         = "innova_path_load_activity",
     *      requirements = {"id" = "\d+"},
     *      options      = {"expose" = true}
     * )
     * @Method("GET")
     */
    public function loadActivityAction($nodeId)
    {
        $activity = array();

        $node = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($nodeId);
        if (!empty($node)) {
            $resource = $this->resourceManager->getResourceFromNode($node);
            if (!empty($resource)) {
                $activity['id'] = $resource->getId();
                $activity['name'] = $resource->getTitle();
                $activity['description'] = $resource->getDescription();

                // Primary resources
                $activity['primaryResource'] = null;
                $primaryResource = $resource->getPrimaryResource();
                if (!empty($primaryResource)) {
                    $activity['primaryResource'] = array(
                        'resourceId' => $primaryResource->getId(),
                        'name' => $primaryResource->getName(),
                        'type' => $primaryResource->getMimeType(),
                    );
                }

                // Process activity parameters
                $parameters = $resource->getParameters();
                if (!empty($parameters)) {
                    // Secondary resources
                    $activity['resources'] = array();

                    $secondaryResources = $parameters->getSecondaryResources();
                    if (!empty($secondaryResources)) {
                        foreach ($secondaryResources as $secondaryResource) {
                            $activity['resources'][] = array(
                                'resourceId' => $secondaryResource->getId(),
                                'name' => $secondaryResource->getName(),
                                'type' => $secondaryResource->getMimeType(),
                                'propagateToChildren' => true,
                            );
                        }
                    }

                    // Global Parameters
                    $activity['withTutor'] = $parameters->isWithTutor();
                    $activity['who'] = $parameters->getWho();
                    $activity['where'] = $parameters->getWhere();

                    $activity['durationHours'] = null;
                    $activity['durationMinutes'] = null;

                    $duration = $parameters->getMaxDuration(); // Duration in seconds
                    if (!empty($duration)) {
                        $duration = $duration / 60; // Duration in minutes

                        $activity['durationHours'] = (int) ($duration / 60);
                        $activity['durationMinutes'] = $duration % 60;
                    }
                }
            }
        }

        return new JsonResponse($activity);
    }

    /**
     * Redirect to Activity using Activity ID
     * @param integer $activityId
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return RedirectResponse
     *
     * @Route(
     *      "/show_activity/{activityId}",
     *      name         = "innova_path_show_activity",
     *      requirements = {"id" = "\d+"},
     *      options      = {"expose" = true}
     * )
     * @Method("GET")
     */
    public function showActivityAction($activityId)
    {
        // Retrieve node from Activity id
        $activity = $this->om->getRepository('ClarolineCoreBundle:Resource\Activity')->findOneById($activityId);
        if (empty($activity)) {
            throw new NotFoundHttpException('Unable to find Activity referenced by ID : ' . $activityId);
        }

        $route = $this->router->generate('claro_resource_open', array(
            'node'         => $activity->getResourceNode()->getId(),
            'resourceType' => 'activity'
        ));

        return new RedirectResponse($route);
    }
}
