<?php

namespace Innova\PathBundle\Controller;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

use Innova\PathBundle\Form\Handler\PathHandler;
use Innova\PathBundle\Manager\PathManager;
use Innova\PathBundle\Entity\Path;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
 *      "workspaces/{workspaceId}/tool/path_editor",
 *      name = "innova_path_editor",
 *      service="innova_path.controller.path_editor"
 * )
 * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\AbstractWorkspace", options={"mapping": {"workspaceId": "id"}})
 */
class EditorController
{
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
     * Path manger
     * @var \Innova\PathBundle\Manager\PathManager $pathManager
     */
    protected $pathManager;
    
    /**
     * Path form handler
     * @var \Innova\PathBundle\Form\Handler\PathHandler
     */
    protected $pathHandler;
    
    /**
     * Class constructor
     * @param \Symfony\Component\Routing\RouterInterface   $router
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Innova\PathBundle\Manager\PathManager       $pathManager
     * @param \Innova\PathBundle\Form\Handler\PathHandler  $pathHandler
     */
    public function __construct(
        RouterInterface      $router,
        FormFactoryInterface $formFactory,
        PathManager          $pathManager,
        PathHandler          $pathHandler)
    {
        $this->router      = $router;
        $this->formFactory = $formFactory;
        $this->pathManager = $pathManager;
        $this->pathHandler = $pathHandler;
    }
    
    /**
     * Display editor wizard
     * 
     * @Route(
     *      "/{id}",
     *      name         = "innova_path_editor_display",
     *      requirements = {"id" = "\d+"},
     *      defaults     = {"id" = null},
     *      options      = {"expose" = true}
     * )
     * @Method("GET")
     * @Template("InnovaPathBundle:Editor:main.html.twig")
     */
    public function displayAction(AbstractWorkspace $workspace, Path $path = null)
    {
        if (empty($path)) {
            // Create a new path
            $params = array (
                'action' => $this->router->generate('innova_path_editor_new', array ('workspaceId' => $workspace->getId())),
            );
            $path = $this->pathManager->initNewPath();
        }
        else {
            // Edit existing path
            $params = array (
                'action' => $this->router->generate('innova_path_editor_edit', array ('workspaceId' => $workspace->getId(), 'id' => $path->getId())),
                'method' => 'PUT',
            );
        }
        
        // Create form
        $form = $this->formFactory->create('innova_path', $path, $params);
        
        return array (
            'workspace' => $workspace,
            'form' => $form->createView(),
        );
    }
    
    /**
     * Save a new path
     * @Route(
     *      "/",
     *      name    = "innova_path_editor_new",
     *      options = {"expose" = true}
     * )
     * @Method("POST")
     */
    public function newAction(AbstractWorkspace $workspace)
    {
        // Create form
        $form = $this->formFactory->create('innova_path', $this->pathManager->initNewPath());
        
        // Try to process data
        $this->pathHandler->setForm($form);
        if ($this->pathHandler->process()) {
            // Redirect to list
            $url = $this->router->generate('claro_workspace_open_tool', array (
                'workspaceId' => $workspace->getId(), 
                'toolName' => 'innova_path'
            ));
        
            return new RedirectResponse($url);
        }
        else {
            // There are some errors => redirect to form
            $url = $this->router->generate('innova_path_editor_display', array (
                'workspaceId' => $workspace->getId(),
            ));
        }
        
        return new RedirectResponse($url);
    }
    
    /**
     * Save an existing path
     * @Route(
     *      "/{id}",
     *      name         = "innova_path_editor_edit",
     *      requirements = {"id" = "\d+"},
     *      options      = {"expose" = true}
     * )
     * @Method("PUT")
     */
    public function editAction(AbstractWorkspace $workspace, Path $path)
    {
        // Create form
        $form = $this->formFactory->create('innova_path', $path);
        
        // Try to process data
        $this->pathHandler->setForm($form);
        if ($this->pathHandler->process()) {
            // Redirect to list
            $url = $this->router->generate('claro_workspace_open_tool', array (
                'workspaceId' => $workspace->getId(),
                'toolName' => 'innova_path'
            ));
        
            return new RedirectResponse($url);
        }
        else {
            // There are some errors => redirect to form
            $url = $this->router->generate('innova_path_editor_display', array (
                'workspaceId' => $workspace->getId(),
                'id' => $path->getId(),
            ));
        }
        
        return new RedirectResponse($url);
    }
}