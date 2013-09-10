<?php

namespace Claroline\CoreBundle\Controller\Log;

use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Event\Log\LogResourceChildUpdateEvent;
use Claroline\CoreBundle\Form\Log\LogWorkspaceWidgetConfigType;
use Claroline\CoreBundle\Form\Log\LogDesktopWidgetConfigType;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Log\LogWorkspaceWidgetConfig;
use Claroline\CoreBundle\Entity\Log\LogDesktopWidgetConfig;
use Claroline\CoreBundle\Entity\Log\LogHiddenWorkspaceWidgetConfig;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Controller of the user profile.
 */
class LogController extends Controller
{
    private $toolManager;
    private $workspaceManager;
    private $eventDispatcher;
    private $security;
    private $formFactory;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "eventDispatcher"    = @DI\Inject("event_dispatcher"),
     *     "security"           = @DI\Inject("security.context"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "translator"         = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ToolManager $toolManager,
        WorkspaceManager $workspaceManager,
        EventDispatcher $eventDispatcher,
        SecurityContextInterface $security,
        FormFactory $formFactory,
        Translator $translator
    )
    {
        $this->toolManager      = $toolManager;
        $this->workspaceManager = $workspaceManager;
        $this->eventDispatcher  = $eventDispatcher;
        $this->security         = $security;
        $this->formFactory      = $formFactory;
        $this->translator       = $translator;
    }

    /**
     * @EXT\Route(
     *     "/view_details/{logId}",
     *           name="claro_log_view_details",
     *           options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "log",
     *           class="ClarolineCoreBundle:Log\Log",
     *           options={"id" = "logId", "strictId" = true}
     * )
     *
     * Displays the public profile of an user.
     *
     * @param \Claroline\CoreBundle\Entity\Log\Log $log
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewDetailsAction(Log $log)
    {
        $eventLogName = 'create_log_details_' . $log->getAction();

        if ($this->eventDispatcher->hasListeners($eventLogName)) {
            $event = $this->eventDispatcher->dispatch(
                $eventLogName,
                new LogCreateDelegateViewEvent($log)
            );

            return new Response($event->getResponseContent());
        }

        return $this->render(
            'ClarolineCoreBundle:Log:view_details.html.twig',
            array('log' => $log)
        );
    }

    /**
     * @EXT\Route(
     *     "/update_workspace_widget_config/{isDefault}/{workspaceId}/{redirectToHome}",
     *     name="claro_log_update_workspace_widget_config",
     *     defaults={"isDefault" = 0, "workspaceId" = 0, "redirectToHome" = 0}
     * )
     * @EXT\Method("POST")
     */
    public function updateLogWorkspaceWidgetConfig($isDefault, $workspaceId, $redirectToHome)
    {
        $isDefault = (boolean) $isDefault;
        $redirectToHome = (boolean) $redirectToHome;

        $entityManager = $this->getDoctrine()->getManager();

        if ($isDefault) {
            $workspace = null;
            $config = $this->get('claroline.log.manager')->getDefaultWorkspaceWidgetConfig();
        } else {
            $workspace = $entityManager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
            $config = $this->get('claroline.log.manager')->getWorkspaceWidgetConfig($workspace);
        }

        if ($config === null) {
            $config = new LogWorkspaceWidgetConfig();
            $config
                ->setIsDefault($isDefault)
                ->setWorkspace($workspace)
                ->setRestrictions($this->get('claroline.log.manager')->getDefaultWorkspaceConfigRestrictions());
        }

        $form = $this->get('form.factory')->create($this->get('claroline.form.logWorkspaceWidgetConfig'), null);

        $form->bind($this->getRequest());
        $translator = $this->get('translator');
        if ($form->isValid()) {
            $data   = $form->getData();

            $entityManager->persist($config);
            $entityManager->flush();

            $this
                ->get('session')
                ->getFlashBag()
                ->add('success', $translator->trans('Your changes have been saved', array(), 'platform'));
        } else {
            $this
                ->get('session')
                ->getFlashBag()
                ->add('error', $translator->trans('The form is not valid', array(), 'platform'));
        }
        $tool = $entityManager->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName('home');

        if ($isDefault === true) {
            $widget = $entityManager->getRepository('ClarolineCoreBundle:Widget\Widget')
                ->findOneBy(array('name' => 'core_resource_logger'));

            return $this->redirect(
                $this->generateUrl(
                    'claro_admin_widget_configuration_workspace', array('widgetId' => $widget->getId())
                )
            );
        } elseif ($redirectToHome === false) {
            return $this->render(
                'ClarolineCoreBundle:Log:config_workspace_form_update.html.twig', array(
                'form' => $form->createView(),
                'workspace' => $workspace,
                'tool' => $tool,
                'isDefault' => $config->getIsDefault() ? 1 : 0
                )
            );
        } else {
            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_open_tool', array('workspaceId' => $workspaceId, 'toolName' => 'home')
                )
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/update_desktop_widget_config/{isDefault}/{redirectToHome}",
     *     name="claro_log_update_desktop_widget_config",
     *     defaults={"isDefault" = 0, "redirectToHome" = 0}
     * )
     * @EXT\Method("POST")
     */
    public function updateDesktopWidgetConfig($isDefault, $redirectToHome)
    {
        $isDefault = (bool) $isDefault;
        $redirectToHome = (bool) $redirectToHome;

        $em = $this->getDoctrine()->getManager();

        if ($isDefault) {
            $user = null;
            $hiddenConfigs = array();
            $workspaces = array();
        } else {
            $user = $this->security->getToken()->getUser();
            $hiddenConfigs = $em->getRepository('ClarolineCoreBundle:Log\LogHiddenWorkspaceWidgetConfig')
                ->findBy(array('user' => $user));
            $workspaces = $this->workspaceManager
                ->getWorkspacesByUserAndRoleNames($user, array('ROLE_WS_COLLABORATOR', 'ROLE_WS_MANAGER'));
        }
        if ($isDefault === true) {
            $config = $this->get('claroline.log.manager')->getDefaultDesktopWidgetConfig();
        } else {
            $config = $this->get('claroline.log.manager')->getDesktopWidgetConfig($user);
        }

        if ($config === null) {
            $config = new LogDesktopWidgetConfig();
            $config->setIsDefault($isDefault);
            $config->setUser($user);
        }

        $form = $this->get('form.factory')->create(
            new LogDesktopWidgetConfigType(),
            null,
            array('workspaces' => $workspaces)
        );
        $form->bind($this->getRequest());

        if ($form->isValid()) {
            $data = $form->getData();
            // remove all hiddenConfigs for user
            foreach ($hiddenConfigs as $hiddenConfig) {
                $em->remove($hiddenConfig);
            }
            $em->flush();
            // add hiddenConfigs from formData for user
            foreach ($data as $workspaceId => $visible) {
                if ($workspaceId != 'amount' and $visible !== true) {
                    $hiddenConfig = new LogHiddenWorkspaceWidgetConfig();
                    $hiddenConfig->setUser($user);
                    $hiddenConfig->setWorkspaceId($workspaceId);
                    $em->persist($hiddenConfig);
                }
            }
            // save amount
            $config->setAmount($data['amount']);
            $em->persist($config);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->translator->trans('Your changes have been saved', array(), 'platform')
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->translator->trans('The form is not valid', array(), 'platform')
            );
        }
        $tool = $this->toolManager->getOneToolByName('home');

        if ($isDefault === true) {
            $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
                ->findOneBy(array('name' => 'core_resource_logger'));

            return $this->redirect(
                $this->generateUrl(
                    'claro_admin_widget_configuration_desktop', array('widgetId' => $widget->getId())
                )
            );
        } elseif ($redirectToHome === false) {
            return $this->render(
                'ClarolineCoreBundle:Log:config_desktop_form_update.html.twig', array(
                    'form' => $form->createView(),
                    'tool' => $tool,
                    'isDefault' => $config->getIsDefault() ? 1 : 0
                )
            );
        } else {
            return $this->redirect($this->generateUrl('claro_desktop_open', array()));
        }
    }
}
