<?php

namespace Innova\PathBundle\Controller;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

use Innova\PathBundle\Form\Handler\PathHandler;
use Innova\PathBundle\Entity\Path\Path;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
 * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\AbstractWorkspace", options={"mapping": {"workspaceId": "id"}})
 */
class EditorController
{
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
     * Current request
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * Current security context
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $security;

    /**
     * Class constructor
     * @param \Doctrine\Common\Persistence\ObjectManager                $objectManager
     * @param \Symfony\Component\Routing\RouterInterface                $router
     * @param \Symfony\Component\Form\FormFactoryInterface              $formFactory
     * @param \Innova\PathBundle\Form\Handler\PathHandler               $pathHandler
     * @param \Claroline\CoreBundle\Manager\ResourceManager             $resourceManager
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     */
    public function __construct(
        ObjectManager        $objectManager,
        RouterInterface      $router,
        FormFactoryInterface $formFactory,
        SessionInterface     $session,
        TranslatorInterface  $translator,
        PathHandler          $pathHandler,
        ResourceManager      $resourceManager,
        SecurityContextInterface $securityContext)
    {
        $this->om              = $objectManager;
        $this->router          = $router;
        $this->formFactory     = $formFactory;
        $this->session         = $session;
        $this->translator      = $translator;
        $this->pathHandler     = $pathHandler;
        $this->resourceManager = $resourceManager;
        $this->security        = $securityContext;
    }

    public function setRequest(Request $request = null)
    {
        $this->request = $request;

        return $this;
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
    public function newAction(AbstractWorkspace $workspace)
    {
        $path = Path::initialize();

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
    public function editAction(AbstractWorkspace $workspace, Path $path)
    {
        return $this->renderEditor($workspace, $path, 'PUT');
    }

    /**
     * Render Editor UI
     * @param  \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @param  string $httpMethod
     * @return array|RedirectResponse
     */
    protected function renderEditor(AbstractWorkspace $workspace, Path $path, $httpMethod = null)
    {
        $params = array ();
        if (!empty($httpMethod)) {
            $params['method'] = $httpMethod;
        }
        // Create form
        $form = $this->formFactory->create('innova_path', $path, $params);

        // Try to process data
        $this->pathHandler->setForm($form);
        if ($this->pathHandler->process()) {
            // Add user message
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('path_save_success', array(), 'path_editor')
            );

            // Redirect to list
            $url = $this->router->generate('innova_path_editor_edit', array (
                'workspaceId' => $workspace->getId(),
                'id' => $path->getId(),
            ));

            return new RedirectResponse($url);
        }

        // Get workspace root directory
        $wsDirectory = $this->resourceManager->getWorkspaceRoot($workspace);
        $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $resourceIcons = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findByIsShortcut(false);

        return array (
            'workspace'          => $workspace,
            'wsDirectoryId'      => $wsDirectory->getId(),
            'resourceTypes'      => $resourceTypes,
            'resourceIcons'      => $resourceIcons,
            'form'               => $form->createView(),
        );
    }
}