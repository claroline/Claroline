<?php

namespace Claroline\CoreBundle\Listener\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogGroupDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogResourceDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogUserDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceRoleDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogNotRepeatableInterface;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class LogListener
{
    private $om;
    private $securityContext;
    private $container;
    private $roleManager;

    /**
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "context"        = @DI\Inject("security.context"),
     *     "container"      = @DI\Inject("service_container"),
     *     "roleManager"    = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        SecurityContextInterface $context,
        $container,
        RoleManager $roleManager
    )
    {
        $this->om = $om;
        $this->securityContext = $context;
        $this->container = $container;
        $this->roleManager = $roleManager;
    }

    private function createLog(LogGenericEvent $event)
    {
        //why is the flush required ?
        //$om->flush();
        //Add doer details
        $token = $this->securityContext->getToken();
        $doer = null;
        $sessionId = null;
        $doerIp = null;
        $doerType = null;

        if ($token === null) {
            $doer = null;
            $doerType = Log::doerTypePlatform;
        } else {
            if ($token->getUser() === 'anon.') {
                $doer = null;
                $doerType = Log::doerTypeAnonymous;
            } else {
                $doer = $token->getUser();
                $doerType = Log::doerTypeUser;
            }
            $request = $this->container->get('request');
            $sessionId = $request->getSession()->getId();
            $doerIp = $request->getClientIp();
        }

        $log = new Log();

        //Simple type properties
        $log->setAction($event->getAction());
        $log->setToolName($event->getToolName());

        //Object properties
        $log->setOwner($event->getOwner());
        if (!($event->getAction() === LogUserDeleteEvent::ACTION && $event->getReceiver() === $doer)) {
            //Prevent self delete case
            $log->setDoer($doer);
        }
        $log->setDoerType($doerType);

        $log->setDoerIp($doerIp);
        if ($event->getAction() !== LogUserDeleteEvent::ACTION) {
            //Prevent user delete case
            $log->setReceiver($event->getReceiver());
        }
        if ($event->getAction() !== LogGroupDeleteEvent::ACTION) {
            $log->setReceiverGroup($event->getReceiverGroup());
        }
        if (
            !(
                $event->getAction() === LogResourceDeleteEvent::ACTION &&
                $event->getResource() === $event->getWorkspace()
            )
        ) {
            //Prevent delete workspace case
            $log->setWorkspace($event->getWorkspace());
        }
        if ($event->getAction() !== LogResourceDeleteEvent::ACTION) {
            //Prevent delete resource case
            $log->setResourceNode($event->getResource());
        }
        if ($event->getAction() !== LogWorkspaceRoleDeleteEvent::ACTION) {
            //Prevent delete role case
            $log->setRole($event->getRole());
        }

        if ($doer !== null) {
            $platformRoles = $this->roleManager->getPlatformRoles($doer);

            foreach ($platformRoles as $platformRole) {
                $log->addDoerPlatformRole($platformRole);
            }

            if ($event->getWorkspace() !== null) {
                $workspaceRoles = $this->roleManager->getWorkspaceRolesForUser($doer, $event->getWorkspace());

                foreach ($workspaceRoles as $workspaceRole) {
                    $log->addDoerWorkspaceRole($workspaceRole);
                }
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
                'firstName' => $doer->getFirstName(),
                'lastName' => $doer->getLastName(),
                'sessionId' => $sessionId
            );

            if (count($log->getDoerPlatformRoles()) > 0) {
                $doerPlatformRolesDetails = array();
                foreach ($log->getDoerPlatformRoles() as $platformRole) {
                    $doerPlatformRolesDetails[] = $platformRole->getTranslationKey();
                }
                $details['doer']['platformRoles'] = $doerPlatformRolesDetails;
            }
            if (count($log->getDoerWorkspaceRoles()) > 0) {
                $doerWorkspaceRolesDetails = array();
                foreach ($log->getDoerWorkspaceRoles() as $workspaceRole) {
                    $doerWorkspaceRolesDetails[] = $workspaceRole->getTranslationKey();
                }
                $details['doer']['workspaceRoles'] = $doerWorkspaceRolesDetails;
            }
        }
        $log->setDetails($details);

        $this->om->persist($log);
        $this->om->flush();
    }

    /**
     * Is a repeat if the session contains a same logSignature for the same action category
     */
    private function isARepeat(LogGenericEvent $event)
    {
        if ($this->securityContext->getToken() === null) {
            //Only if have a user session;
            return false;
        }

        if ($event instanceof LogNotRepeatableInterface) {
            $request = $this->container->get('request');
            $session = $request->getSession();

            $is = false;
            $pushInSession = true;
            $now = time();

            //if ($session->get($event->getAction()) != null) {
            if ($session->get($event->getLogSignature()) != null) {
                //$oldArray = json_decode($session->get($event->getAction()));
                $oldArray = json_decode($session->get($event->getLogSignature()));
                $oldSignature = $oldArray->logSignature;
                $oldTime = $oldArray->time;

                if ($oldSignature == $event->getLogSignature()) {
                    $diff = ($now - $oldTime);
                    if ($diff > $this->container->getParameter('non_repeatable_log_time_in_seconds')) {
                        $is = false;
                    } else {
                        $is = true;
                        $pushInSession = false;
                    }
                }
            }

            if ($pushInSession) {
                //Update last logSignature for this event category
                $array = array('logSignature' => $event->getLogSignature(), 'time' => $now);
                //$session->set($event->getAction(), json_encode($array));
                $session->set($event->getLogSignature(), json_encode($array));
            }

            return $is;
        } else {
            return false;
        }
    }

    /**
     * @DI\Observe("log")
     *
     * @param WorkspaceLogEvent $event
     */
    public function onLog(LogGenericEvent $event)
    {
        if (!($event instanceof LogNotRepeatableInterface) or !$this->isARepeat($event)) {
            $this->createLog($event);
        }
    }
}
