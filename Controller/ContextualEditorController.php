<?php

namespace Innova\PathBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerAware;


use Innova\PathBundle\Form\Handler\StepHandler;
use Innova\PathBundle\Entity\Path;
use Innova\PathBundle\Entity\Step;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;


/**
 * ContextualEditorController controller
 * @author Innovalangues <contact@innovalangues.net>
 * 
 * @Route(
 *      "",
 *      name="innova_path_contextual_editor",
 *      service="innova_path.controller.path_contextual_editor"
 * )
 */
class ContextualEditorController extends ContainerAware
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
     * @var \Innova\PathBundle\Form\Handler\StepHandler
     */
    protected $stepHandler;

    /**
     * Authenticated user
     * @var \Claroline\CoreBundle\Entity\User\User $user
     */
    protected $user;

    /**
     * Current security context
     * @var \Symfony\Component\Security\Core\SecurityContext $security
     */
    protected $security;
    
    /**
     * Class constructor
     * @param \Symfony\Component\Routing\RouterInterface   $router
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Innova\PathBundle\Form\Handler\StepHandler  $stepHandler
     */
    public function __construct(
        securityContext      $securityContext, 
        RouterInterface      $router,
        FormFactoryInterface $formFactory,
        SessionInterface     $session,
        TranslatorInterface  $translator,
        StepHandler          $stepHandler)
    {
        $this->security    = $securityContext;
        $this->router      = $router;
        $this->formFactory = $formFactory;
        $this->session     = $session;
        $this->translator  = $translator;
        $this->stepHandler = $stepHandler;

        $this->user = $this->security->getToken()->getUser();
    }


    /**
     * Display path player
     * @param  Path $path
     * @return array
     * 
     * @Route(
     *      "workspace/{workspaceId}/path/{pathId}/step/{stepId}/edit/",
     *      name="innova_path_contextual_editor",
     *      options={"expose" = true}
     * )
     * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\AbstractWorkspace", options={"mapping": {"workspaceId": "id"}})
     * @ParamConverter("path", class="InnovaPathBundle:Path", options={"mapping": {"pathId": "id"}})
     * @ParamConverter("currentStep", class="InnovaPathBundle:Step", options={"mapping": {"stepId": "id"}})
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:main.html.twig")
     */
    public function displayAction(AbstractWorkspace $workspace, Path $path, Step $currentStep)
    {
        $pathCreator = $path->getResourceNode()->getCreator();
            
        if ($pathCreator != $this->user) {
            $this->addflashMessage('warning', $this->translator->trans('save_warning_not_owner', array(), 'path_player'));
            $url = $this->container->get('router')->generate('innova_path_player_index', array('stepId' => $currentStep->getId(), 'workspaceId' => $workspace->getId(), 'pathId' => $path->getId()));

            return new RedirectResponse($url);
        }
        
        return array (
            'workspace' => $workspace,
            'path' => $path,
            'currentStep' => $currentStep,
            'edit' => true,
        );
    }

    /**
     * Edit step
     * 
     * @Route(
     *      "workspace/{workspaceId}/path/{pathId}/step/{stepId}/edit/",
     *      name="innova_path_save_current_step",
     *      options={"expose" = true}
     * )
     * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\AbstractWorkspace", options={"mapping": {"workspaceId": "id"}})
     * @ParamConverter("path", class="InnovaPathBundle:Path", options={"mapping": {"pathId": "id"}})
     * @ParamConverter("currentStep", class="InnovaPathBundle:Step", options={"mapping": {"stepId": "id"}})
     * @Method("POST")
     */
    public function editStepAction(AbstractWorkspace $workspace, Path $path, Step $currentStep)
    {

        $form = $this->container->get('form.factory')->create('innova_step', $currentStep);

        if(!$path->isModified())
        {
            $this->stepHandler->setForm($form);
            if ($this->stepHandler->process())
            {
                $this->addflashMessage('success', $this->translator->trans('save_success', array(), 'path_player'));
            } else {
                $this->addflashMessage('warning', $this->translator->trans('save_warning', array(), 'path_player'));
            }
        } else {
            $this->addflashMessage('warning', $this->translator->trans('save_already_modified', array(), 'path_player'));
        }

        $url = $this->container->get('router')->generate('innova_path_player_index', array('stepId' => $currentStep->getId(), 'workspaceId' => $workspace->getId(), 'pathId' => $path->getId()));

        return new RedirectResponse($url);
    }

    public function addflashMessage($class, $message){

        return $this->session->getFlashBag()->add('alert alert-'.$class, $message);
    }
}