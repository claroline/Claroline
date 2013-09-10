<?php

namespace Claroline\CoreBundle\Controller\Widget;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Entity\Widget\SimpleTextWorkspaceConfig;
use Claroline\CoreBundle\Entity\Widget\SimpleTextDesktopConfig;
use Claroline\CoreBundle\Entity\User;

class SimpleTextController extends Controller
{
    /**
     * @EXT\Route(
     *     "/simple_text_update/config/{isDefault}/workspace/{workspaceId}/{redirectToHome}",
     *     name="claro_simple_text_update_workspace_widget_config",
     *     defaults={"isDefault" = 0, "workspaceId" = 0, "redirectToHome" = 0}
     * )
     * @EXT\Method("POST")
     */
    public function updateLogWorkspaceWidgetConfig($isDefault, $workspaceId, $redirectToHome)
    {
        $isDefault = (boolean) $isDefault;
        $redirectToHome = (boolean) $redirectToHome;

        $em = $this->getDoctrine()->getManager();

        if ($isDefault) {
            $workspace = null;
            $config = $this->get('claroline.manager.simple_text_manager')->getDefaultWorkspaceWidgetConfig();
        } else {
            $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
            $config = $this->get('claroline.manager.simple_text_manager')->getWorkspaceWidgetConfig($workspace);
        }

        if ($config === null) {
            $config = new SimpleTextWorkspaceConfig();
            $config->setIsDefault($isDefault);
            $config->setWorkspace($workspace);
        }

        $form = $this->get('claroline.form.factory')->create(FormFactory::TYPE_SIMPLE_TEXT);

        $form->bind($this->getRequest());
        $translator = $this->get('translator');
        if ($form->isValid()) {
            $config->setContent($form->get('content')->getData());
            $em->persist($config);
            $em->flush();

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
        $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName('home');

        if ($isDefault === true) {
            $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
                ->findOneBy(array('name' => 'simple_text'));

            return $this->redirect(
                $this->generateUrl(
                    'claro_admin_widget_configuration_workspace', array('widgetId' => $widget->getId())
                )
            );

        } elseif ($redirectToHome === false) {
            return $this->render(
                'ClarolineCoreBundle:Widget:config_workspace_widget_simple_text_form.html.twig', array(
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
     *     "/simple_text_update/config/{isDefault}/{redirectToHome}",
     *     name="claro_simple_text_update_workspace_widget_config",
     *     defaults={"isDefault" = 0, "redirectToHome" = 0}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function updateDesktopWidgetConfig($isDefault, $redirectToHome, User $user)
    {
        if ($isDefault === true) {
            $config = $this->get('claroline.manager.simple_text_manager')->getDefaultDesktopWidgetConfig();
        } else {
            $config = $this->get('claroline.manager.simple_text_manager')->getDesktopWidgetConfig($user);
        }

        if ($config === null) {
            $config = new SimpleTextDesktopConfig();
            $config->setIsDefault($isDefault);
            $config->setUser($user);
        }

        $form = $this->get('claroline.form.factory')->create(FormFactory::TYPE_SIMPLE_TEXT);
        $form->bind($this->getRequest());
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');

        if ($form->isValid()) {
            $config->setContent($form->get('content')->getData());
            $em->persist($config);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                $translator->trans('Your changes have been saved', array(), 'platform')
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                'error',
                $translator->trans('The form is not valid', array(), 'platform')
            );
        }

        if ($isDefault === true) {
            $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
                ->findOneBy(array('name' => 'simple_text'));

            return $this->redirect(
                $this->generateUrl(
                    'claro_admin_widget_configuration_desktop', array('widgetId' => $widget->getId())
                )
            );
        } elseif ($redirectToHome === false) {
            return $this->render(
                'ClarolineCoreBundle:Widget:config_workspace_widget_simple_text_form.html.twig', array(
                    'form' => $form->createView(),
                    'tool' => $this->toolManager->getOneToolByName('home'),
                    'isDefault' => $config->getIsDefault() ? 1 : 0
                )
            );
        } else {
            return $this->redirect($this->generateUrl('claro_desktop_open', array()));
        }
    }
}