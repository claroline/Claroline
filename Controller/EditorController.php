<?php

namespace Innova\PathBundle\Controller;

use Innova\PathBundle\Manager\PathManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Innova\PathBundle\Form\Handler\PathHandler;
use Innova\PathBundle\Entity\Path\Path;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Innova\PathBundle\Entity\Path\PathTemplate;
use Innova\PathBundle\Manager\PathTemplateManager;

/**
 * Class EditorController
 *
 * @category   Controller
 * @package    Innova
 * @subpackage PathBundle
 * @author     Innovalangues <contact@innovalangues.net>
 * @copyright  2013 Innovalangues
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    0.1
 * @link       http://innovalangues.net
 *
 * @Route(
 *      "workspaces/{workspaceId}/tool/path",
 *      name    = "innova_path_editor",
 *      service = "innova_path.controller.path_editor"
 * )
 * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\Workspace", options={"mapping": {"workspaceId": "id"}})
 */
class EditorController {

    /**
     * Object manager
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * Router
     * @var \Symfony\Component\Routing\RouterInterface $router
     */
    protected $router;

    /**
     * Form factory
     * @var \Symfony\Component\Form\FormFactoryInterface $formFactory
     */
    protected $formFactory;

    /**
     * Session
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * Translator engine
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * Path form handler
     * @var \Innova\PathBundle\Form\Handler\PathHandler
     */
    protected $pathHandler;

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
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     * @param \Innova\PathBundle\Form\Handler\PathHandler $pathHandler
     * @param \Innova\PathBundle\Manager\PathManager $pathManager
     * @param \Claroline\CoreBundle\Manager\ResourceManager $resourceManager
     */
    public function __construct(
    ObjectManager $objectManager, RouterInterface $router, FormFactoryInterface $formFactory, SessionInterface $session, TranslatorInterface $translator, PathHandler $pathHandler, PathManager $pathManager, ResourceManager $resourceManager) {
        $this->om = $objectManager;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->session = $session;
        $this->translator = $translator;
        $this->pathHandler = $pathHandler;
        $this->pathManager = $pathManager;
        $this->resourceManager = $resourceManager;
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
    public function newAction(Workspace $workspace) {
        $path = Path::initialize();
        $this->pathManager->checkAccess('CREATE', $path, $workspace);

        return $this->renderEditor($workspace, $path);
    }

   
    /**
     * Create a new path from a template
     * 
     * @param  \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * 
     * @Route(
     *      "/new_from_template/{templateId}",
     *      name    = "innova_path_editor_create_from_template",
     *      options = {"expose" = true}
     * )
     * @Method({"GET", "POST"})
     * @ParamConverter("template", class="InnovaPathBundle:Path\PathTemplate", options={"mapping": {"templateId": "id"}})
     * @Template("InnovaPathBundle:Editor:main.html.twig")
     */
    public function newFromModelAction(Workspace $workspace, PathTemplate $template) {
        $path = new Path();
        
        $this->pathManager->checkAccess('CREATE', $path, $workspace);

        $path->setName($template->getName());
        
        $structure = $template->getStructure();
        $path->setDescription($template->getDescription());
        
        $path->initializeStructure(json_decode($structure, true));
        echo $path->getStructure();die;
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
    public function editAction(Workspace $workspace, Path $path) {
        $this->pathManager->checkAccess('EDIT', $path);

        return $this->renderEditor($workspace, $path, 'PUT');
    }

    /**
     * Render Editor UI
     * @param  \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @param  string $httpMethod
     * @return array|RedirectResponse
     */
    protected function renderEditor(Workspace $workspace, Path $path, $httpMethod = null) {
        $params = array();
        if (!empty($httpMethod)) {
            $params['method'] = $httpMethod;
        }
        // Create form
        $form = $this->formFactory->create('innova_path', $path, $params);

        // Add save and close flag to form
        $form->add('saveAndClose', 'hidden', array('mapped' => false));

        // Try to process data
        $this->pathHandler->setForm($form);
        if ($this->pathHandler->process()) {
            // Add user message
            $this->session->getFlashBag()->add(
                'success', $this->translator->trans('path_save_success', array(), 'path_editor')
            );

            $saveAndClose = $form->get('saveAndClose')->getData();
            $saveAndClose = filter_var($saveAndClose, FILTER_VALIDATE_BOOLEAN);

            if (!$saveAndClose) {
                // Redirect to editor
                $url = $this->router->generate('innova_path_editor_edit', array(
                    'workspaceId' => $workspace->getId(),
                    'id' => $path->getId(),
                ));
            } else {
                // Redirect to list of paths
                $url = $this->router->generate('claro_workspace_open_tool', array(
                    'workspaceId' => $workspace->getId(),
                    'toolName' => 'innova_path',
                ));
            }

            // Redirect to list

            return new RedirectResponse($url);
        }

        // Get workspace root directory
        $wsDirectory = $this->resourceManager->getWorkspaceRoot($workspace);
        $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $resourceIcons = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findByIsShortcut(false);

        return array(
            'workspace' => $workspace,
            'wsDirectoryId' => $wsDirectory->getId(),
            'resourceTypes' => $resourceTypes,
            'resourceIcons' => $resourceIcons,
            'form' => $form->createView(),
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
    public function loadActivityAction($nodeId) {
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
    public function showActivityAction($activityId) {
        // Retrieve node from Activity id
        $activity = $this->om->getRepository('ClarolineCoreBundle:Resource\Activity')->findOneById($activityId);
        if (empty($activity)) {
            throw new NotFoundHttpException('Unable to find Activity referenced by ID : ' . $activityId);
        }

        $route = $this->router->generate('claro_resource_open', array('node' => $activity->getResourceNode()->getId(), 'resourceType' => 'activity'));

        return new RedirectResponse($route);
    }

}
