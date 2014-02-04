<?php

namespace Innova\PathBundle\Controller;

use Symfony\Component\Form\FormFactoryInterface;
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
     * Class constructor
     * @param \Symfony\Component\Routing\RouterInterface   $router
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Innova\PathBundle\Form\Handler\PathHandler  $pathHandler
     */
    public function __construct(
        RouterInterface      $router,
        FormFactoryInterface $formFactory,
        SessionInterface     $session,
        TranslatorInterface  $translator,
        PathHandler          $pathHandler)
    {
        $this->router      = $router;
        $this->formFactory = $formFactory;
        $this->session     = $session;
        $this->translator  = $translator;
        $this->pathHandler = $pathHandler;
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
        
        // Create form
        $form = $this->formFactory->create('innova_path', $path);
        
        // Try to process data
        $this->pathHandler->setForm($form);
        if ($this->pathHandler->process()) {
            // Add user message
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('path_save_success', array(), 'path_editor')
            );
            
            // Redirect to edit
            $url = $this->router->generate('innova_path_editor_edit', array (
                'workspaceId' => $workspace->getId(),
                'id' => $path->getId(),
            ));
        
            return new RedirectResponse($url);
        }
        
        return array (
            'workspace' => $workspace,
            'form'      => $form->createView(),
        );
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
        // Create form
        $form = $this->formFactory->create('innova_path', $path, array ('method' => 'PUT'));
        
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
        
        return array (
            'workspace' => $workspace,
            'form'      => $form->createView(),
        );
    }
}