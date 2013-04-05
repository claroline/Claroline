<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Event\LogGenericEvent;
use Claroline\CoreBundle\Library\Event\LogUserCreateEvent;
use Claroline\CoreBundle\Library\Event\LogGroupDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogResourceDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogUserDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogWorkspaceRoleDeleteEvent;
use Claroline\CoreBundle\Entity\Logger\Log;

class LogListener extends ContainerAware
{
    public function onLog(LogGenericEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->flush();

        $logger = $this->container->get('logger');
        $logger->info('onLogResource');

        //Add doer details
        $token = $this->container->get('security.context')->getToken();
        $doer = null;
        $sessionId = null;
        $doerIp = null;
        if ($token === null) {
            if ($event->getAction() === LogUserCreateEvent::action) {
                //Handling the case where a user creates his own account (without being already logged)
                $doer = $event->getReceiver();
            } else {
                //For manage data fixture case the doer must be nullable
                $doer = null;
            }
        } else {
            $doer = $token->getUser();
            $request = $this->container->get('request');
            $sessionId = $request->getSession()->getId();
            $doerIp = $request->getClientIp();
        }

        $log = new Log();

        //Simple type properties
        $log->setAction($event->getAction());
        $log->setChildType($event->getChildType());
        $log->setChildAction($event->getChildAction());

        //Object properties
        $log->setOwner($event->getOwner());
        if (!($event->getAction() === LogUserDeleteEvent::action && $event->getReceiver() === $doer)) {
            //Prevent self delete case
            $log->setDoer($doer);
        }
        $log->setDoerIp($doerIp);
        if ($event->getAction() !== LogUserDeleteEvent::action) {
            //Prevent user delete case
            $log->setReceiver($event->getReceiver());
        }
        if ($event->getAction() !== LogGroupDeleteEvent::action) {
            $log->setReceiverGroup($event->getReceiverGroup());
        }
        if (!($event->getAction() === LogResourceDeleteEvent::action && $event->getResource() === $event->getWorkspace())) {
            //Prevent delete workspace case
            $log->setWorkspace($event->getWorkspace());
        }
        if ($event->getAction() !== LogResourceDeleteEvent::action) {
            //Prevent delete resource case
            $log->setResource($event->getResource());
        }
        if ($event->getAction() !== LogWorkspaceRoleDeleteEvent::action) {
            //Prevent delete role case
            $log->setRole($event->getRole());
        }

        if ($doer !== null) {
            $log->addDoerPlatformRole($doer->getPlatformRole());
            if ($event->getWorkspace() !== null) {
                $roleRepository = $em->getRepository('ClarolineCoreBundle:Role');
                $log->addDoerWorkspaceRole($roleRepository->findWorkspaceRoleForUser($doer, $event->getWorkspace()));
            }
        }
        if ($event->getResource() !== null) {
            $log->setResourceType($event->getResource()->getResourceType());
        }

        //Json_array properties
        $details = $event->getDetails();
        if ($details === null) {
            $details = array();
        }

        if ($doer !== null) {
            $details['doer'] = array(
                'first_name' => $doer->getFirstName(),
                'last_name' => $doer->getLastName(),
                'session_id' => $sessionId
            );

            if (count($log->getDoerPlatformRoles()) > 0) {
                $doerPlatformRolesDetails = array();
                foreach ($log->getDoerPlatformRoles() as $platformRole) {
                    $doerPlatformRolesDetails[] = $platformRole->getName();   
                }
                $details['doer']['platform_roles'] = $doerPlatformRolesDetails;
            }
            if (count($log->getDoerWorkspaceRoles()) > 0) {
                $doerWorkspaceRolesDetails = array();
                foreach ($log->getDoerWorkspaceRoles() as $workspaceRole) {
                    $doerWorkspaceRolesDetails[] = $workspaceRole->getName();   
                }
                $details['doer']['workspace_roles'] = $doerWorkspaceRolesDetails;
            }
        }
        $log->setDetails($details);

        $em->persist($log);
        $em->flush();
    }
}