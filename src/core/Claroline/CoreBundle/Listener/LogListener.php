<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Library\Event\LogGenericEvent;
use Claroline\CoreBundle\Library\Event\LogGroupDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogResourceDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogUserDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogWorkspaceRoleDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogNotRepeatableInterface;
use Claroline\CoreBundle\Entity\Logger\Log;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;


/**
 * @DI\Service
 */
class LogListener
{
    private $em;
    private $securityContext;
    private $container;

    /**
     * @DI\InjectParams({
     *     "em"         = @DI\Inject("doctrine.orm.entity_manager"),
     *     "context"    = @DI\Inject("security.context"),
     *     "container"  = @DI\Inject("service_container")
     * })
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em, SecurityContextInterface $context, $container)
    {
        $this->em = $em;
        $this->securityContext = $context;
        $this->container = $container;
    }

    private function createLog(LogGenericEvent $event)
    {
        $this->em->flush();

        //Add doer details
        $token = $this->container->get('security.context')->getToken();
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
        $log->setChildType($event->getChildType());
        $log->setChildAction($event->getChildAction());
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
            $log->setResource($event->getResource());
        }
        if ($event->getAction() !== LogWorkspaceRoleDeleteEvent::ACTION) {
            //Prevent delete role case
            $log->setRole($event->getRole());
        }

        if ($doer !== null) {
            $roleRepository = $this->em->getRepository('ClarolineCoreBundle:Role');
            $platformRoles = $roleRepository->findPlatformRoles($doer);
            foreach ($platformRoles as $platformRole) {
                $log->addDoerPlatformRole($platformRole);
            }

            if ($event->getWorkspace() !== null) {
                $workspaceRole = $roleRepository->findWorkspaceRoleForUser($doer, $event->getWorkspace());
                if ($workspaceRole != null) {
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

        $this->em->persist($log);
        $this->em->flush();
    }

    /**
     * Is a repeat if the session contains a same logSignature for the same action category
     */
    private function isARepeat(LogGenericEvent $event)
    {
        if ($this->container->get('security.context')->getToken() === null) {
            //Only if have a user session;

            return false;
        }

        if ($event instanceof NotRepeatableLog) {
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