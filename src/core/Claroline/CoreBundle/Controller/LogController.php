<?php

namespace Claroline\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Library\Event\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Library\Event\LogResourceChildUpdateEvent;
use Claroline\CoreBundle\Form\LogWorkspaceWidgetConfigType;
use Claroline\CoreBundle\Entity\Logger\LogWorkspaceWidgetConfig;

/**
 * Controller of the user profile.
 */
class LogController extends Controller
{

    private function convertFormDataToConfig($config, $data, $workspace)
    {
        if ($config === null) {
            $config = new LogWorkspaceWidgetConfig();    
        }
        
        $config->setResourceCopy($data['creation'] === true);
        $config->setResourceCreate($data['creation'] === true);
        $config->setResourceShortcut($data['creation'] === true);
        
        $config->setResourceRead($data['read'] === true);
        $config->setWsToolRead($data['read'] === true);

        $config->setResourceExport($data['export'] === true);

        $config->setResourceUpdate($data['update'] === true);
        $config->setResourceUpdateRename($data['update'] === true);

        $config->setResourceChildUpdate($data['updateChild'] === true);

        $config->setResourceDelete($data['delete'] === true);

        $config->setResourceMove($data['move'] === true);

        $config->setWsRoleSubscribeUser($data['subscribe'] === true);
        $config->setWsRoleSubscribeGroup($data['subscribe'] === true);
        $config->setWsRoleUnsubscribeUser($data['subscribe'] === true);
        $config->setWsRoleUnsubscribeGroup($data['subscribe'] === true);
        $config->setWsRoleChangeRight($data['subscribe'] === true);
        $config->setWsRoleCreate($data['subscribe'] === true);
        $config->setWsRoleDelete($data['subscribe'] === true);
        $config->setWsRoleUpdate($data['subscribe'] === true);

        $config->setAmount($data['amount']);

        $config->setWorkspace($workspace);

        return $config;
    }

    /**
     * @Route(
     *     "/view_details/{logId}",
     *     name="claro_log_view_details",
     *     options={"expose"=true}
     * )
     *
     * Displays the public profile of an user.
     *
     * @param integer $userId The id of the user we want to see the profile
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewDetailsAction($logId)
    {
        $em = $this->getDoctrine()->getManager();
        $log = $em->getRepository('ClarolineCoreBundle:Logger\Log')->find($logId);

        if ($log->getAction() === LogResourceChildUpdateEvent::ACTION ) {
            $eventName = 'create_log_details_'.$log->getResourceType()->getName();
            $event = new LogCreateDelegateViewEvent($log);
            $this->container->get('event_dispatcher')->dispatch($eventName, $event);

            if ($event->getResponseContent() === "") {
                throw new \Exception(
                    "Event '{$eventName}' didn't receive any response."
                );
            }

            return new Response($event->getResponseContent());
        }

        return $this->render(
            'ClarolineCoreBundle:Log:view_details.html.twig',
            array('log' => $log)
        );
    }

    /**
     * @Route(
     *     "/update_workspace_widget_config/{workspaceId}",
     *     name="claro_log_update_workspace_widget_config",
     *     options={"expose"=true}
     * )
     */
    public function updateLogWorkspaceWidgetConfig($workspaceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $config = $em->getRepository('ClarolineCoreBundle:Logger\LogWorkspaceWidgetConfig')
            ->findOneBy(array('workspace' => $workspace));

        $form = $this->get('form.factory')->create(new LogWorkspaceWidgetConfigType(), null);

        $form->bind($this->getRequest());
        $translator = $this->get('translator');
        if ($form->isValid()) {
            $this->get('session')->getFlashBag()->add('success', $translator->trans('Your changes have been saved', array(), 'platform'));

            $data = $form->getData();
            $config = $this->convertFormDataToConfig($config, $data, $workspace);

            $em->persist($config);
            $em->flush();
        } else {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('The form is not valid', array(), 'platform'));
        }
        $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName('home');

        return $this->render(
            'ClarolineCoreBundle:Log:config_workspace_form_update.html.twig', array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'tool' => $tool
            )
        );
    }
}