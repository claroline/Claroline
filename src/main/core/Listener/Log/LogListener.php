<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Log;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogAdminToolReadEvent;
use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Event\Log\LogDesktopToolReadEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogGroupDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogNotRepeatableInterface;
use Claroline\CoreBundle\Event\Log\LogResourceDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogUserDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceRoleDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Event\LogCreateEvent;
use Claroline\CoreBundle\Event\Security\UserLoginEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\LogConnectManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogListener
{
    private $om;
    private $tokenStorage;
    /** @var Request */
    private $request;
    private $container;
    private $roleManager;
    private $ch;
    private $enabledLog;
    private $logConnectManager;

    /**
     * LogListener constructor.
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        ContainerInterface $container,
        RoleManager $roleManager,
        PlatformConfigurationHandler $ch,
        LogConnectManager $logConnectManager
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->request = $requestStack->getMasterRequest();
        $this->container = $container;
        $this->roleManager = $roleManager;
        $this->ch = $ch;
        $this->enabledLog = $this->ch->getParameter('platform_log_enabled');
        $this->logConnectManager = $logConnectManager;
    }

    private function createLog(LogGenericEvent $event)
    {
        if (!$this->enabledLog) {
            return null;
        }

        //Add doer details
        $doer = null;
        $doerIp = null;
        $doerSessionId = null;
        $doerType = null;

        //Event can override the doer
        if (null === $event->getDoer()) {
            $token = $this->tokenStorage->getToken();
            if (null === $token) {
                $doer = null;
                $doerType = Log::DOER_PLATFORM;
            } else {
                if (!$token->getUser() instanceof User) {
                    $doer = null;
                    $doerType = Log::DOER_ANONYMOUS;
                } else {
                    $doer = $token->getUser();
                    $doerType = Log::DOER_USER;
                }

                if ($this->request) {
                    $doerSessionId = $this->request->getSession()->getId();
                    $doerIp = $this->request->getClientIp();
                } else {
                    $doerIp = 'CLI';
                }
            }
        } elseif (LogGenericEvent::PLATFORM_EVENT_TYPE === $event->getDoer()) {
            $doer = null;
            $doerType = Log::DOER_PLATFORM;
        } else {
            $doer = $event->getDoer();
            $doerType = Log::DOER_USER;
        }

        $log = new Log();

        //Simple type properties
        $log
            ->setAction($event->getAction())
            ->setToolName($event->getToolName())
            ->setIsDisplayedInAdmin($event->getIsDisplayedInAdmin())
            ->setIsDisplayedInWorkspace($event->getIsDisplayedInWorkspace())
            ->setOtherElementId($event->getOtherElementId());

        if (!(LogUserDeleteEvent::ACTION === $event->getAction() && $event->getReceiver() === $doer)) {
            //Prevent self delete case
            //Sometimes, the entity manager has been cleared, so we must merge the doer.
            if ($doer) {
                $doer = $this->om->merge($doer);
            }
            $log->setDoer($doer);
        }
        $log->setDoerType($doerType);

        $log->setDoerIp($doerIp);
        $log->setDoerSessionId($doerSessionId);
        if (LogUserDeleteEvent::ACTION !== $event->getAction()) {
            //Prevent user delete case
            $log->setReceiver($event->getReceiver());
        }
        if (LogGroupDeleteEvent::ACTION !== $event->getAction()) {
            $receiverGroup = $event->getReceiverGroup();
            if ($receiverGroup) {
                $this->om->merge($receiverGroup);
            }
            $log->setReceiverGroup($receiverGroup);
        }
        if (
            !(
                LogResourceDeleteEvent::ACTION === $event->getAction() &&
                $event->getResource() === $event->getWorkspace()
            )
        ) {
            //Prevent delete workspace case
            $log->setWorkspace($event->getWorkspace());
        }
        if (LogResourceDeleteEvent::ACTION !== $event->getAction()) {
            //Prevent delete resource case
            $log->setResourceNode($event->getResource());
        }
        if (LogWorkspaceRoleDeleteEvent::ACTION !== $event->getAction()) {
            //Prevent delete role case
            $log->setRole($event->getRole());
        }

        if (null !== $event->getResource()) {
            $log->setResourceType($event->getResource()->getResourceType());
        }

        //Json_array properties
        $details = $event->getDetails();

        if (null === $details) {
            $details = [];
        }

        if (null !== $doer) {
            $details['doer'] = [
                'firstName' => $doer->getFirstName(),
                'lastName' => $doer->getLastName(),
                'username' => $doer->getUsername(),
            ];

            $doerPlatformRoles = $this->roleManager->getPlatformRoles($doer);

            if ($event->getWorkspace()) {
                $doerWorkspaceRoles = $this->roleManager->getWorkspaceRolesForUser($doer, $event->getWorkspace());
            } else {
                $doerWorkspaceRoles = [];
            }

            if (count($doerPlatformRoles) > 0) {
                $doerPlatformRolesDetails = [];

                foreach ($doerPlatformRoles as $platformRole) {
                    $doerPlatformRolesDetails[] = $platformRole->getTranslationKey();
                }

                $details['doer']['platformRoles'] = $doerPlatformRolesDetails;
            }

            if (count($doerWorkspaceRoles) > 0) {
                $doerWorkspaceRolesDetails = [];
                foreach ($doerWorkspaceRoles as $workspaceRole) {
                    $doerWorkspaceRolesDetails[] = $workspaceRole->getTranslationKey();
                }
                $details['doer']['workspaceRoles'] = $doerWorkspaceRolesDetails;
            }
        }
        $log->setDetails($details);

        $this->om->persist($log);
        $this->om->flush();

        $createLogEvent = new LogCreateEvent($log);
        $this->container->get('event_dispatcher')->dispatch($createLogEvent, LogCreateEvent::NAME);

        return $log;
    }

    /**
     * Is a repeat if the session contains a same logSignature for the same action category.
     *
     * @return bool
     */
    public function isARepeat(LogGenericEvent $event)
    {
        if (null === $this->tokenStorage->getToken()) {
            //Only if have a user session;
            return false;
        }

        if ($event instanceof LogNotRepeatableInterface) {
            $session = $this->request->getSession();

            $is = false;
            $pushInSession = true;
            $now = time();
            $notRepeatableLogTimeInSeconds = $this->container->getParameter(
                'non_repeatable_log_time_in_seconds'
            );

            // Always logs workspace entering, tool reading and resource reading if the target object is different from the previous one
            if ($event->getIsWorkspaceEnterEvent() || $event->getIsToolReadEvent() || $event->getIsResourceReadEvent()) {
                $workspaceId = !is_null($event->getWorkspace()) ? $event->getWorkspace()->getUuid() : null;
                $key = null;

                if ($event->getIsWorkspaceEnterEvent()) {
                    $key = $workspaceId;
                } elseif ($event->getIsToolReadEvent()) {
                    $key = $event->getToolName();
                } elseif ($event->getIsResourceReadEvent()) {
                    $key = $event->getResource()->getUuid();
                }

                if (!is_null($session->get($event->getAction()))) {
                    $oldArray = json_decode($session->get($event->getAction()));
                    $oldSignature = $oldArray->logSignature;
                    $oldTime = $oldArray->time;

                    if ($oldSignature === $event->getAction()) {
                        $diff = ($now - $oldTime);
                        $oldWorkspaceId = $oldArray->workspaceId;
                        $oldKey = $oldArray->key;

                        if (LogWorkspaceEnterEvent::ACTION === $event->getAction()) {
                            $notRepeatableLogTimeInSeconds = $notRepeatableLogTimeInSeconds * 3;
                        }
                        if (((is_null($oldWorkspaceId) && is_null($workspaceId)) || $oldWorkspaceId === $workspaceId) &&
                            $oldKey === $key &&
                            $diff <= $notRepeatableLogTimeInSeconds &&
                            !$this->hasBreakingRepeatEvent($session, $event)
                        ) {
                            $is = true;
                            $pushInSession = false;
                        }
                    }
                }
                if ($pushInSession) {
                    //Update last log action for this event category
                    $array = [
                        'logSignature' => $event->getAction(),
                        'time' => $now,
                        'workspaceId' => $workspaceId,
                        'key' => $key,
                    ];
                    $session->set($event->getAction(), json_encode($array));
                }
            } else {
                if (null !== $session->get($event->getLogSignature())) {
                    $oldArray = json_decode($session->get($event->getLogSignature()));
                    $oldSignature = $oldArray->logSignature;
                    $oldTime = $oldArray->time;

                    if ($oldSignature === $event->getLogSignature()) {
                        $diff = ($now - $oldTime);

                        if ($diff > $notRepeatableLogTimeInSeconds) {
                            $is = false;
                        } else {
                            $is = true;
                            $pushInSession = false;
                        }
                    }
                }

                if ($pushInSession) {
                    //Update last logSignature for this event category
                    $array = ['logSignature' => $event->getLogSignature(), 'time' => $now];
                    $session->set($event->getLogSignature(), json_encode($array));
                }
            }

            return $is;
        } else {
            return false;
        }
    }

    public function onLog(LogGenericEvent $event)
    {
        $log = null;
        $logCreated = false;

        if (!($event instanceof LogNotRepeatableInterface) || !$this->isARepeat($event)) {
            $log = $this->createLog($event);
            $logCreated = true;
        }

        if ($logCreated && $log && (
            $event instanceof UserLoginEvent ||
            $event instanceof LogWorkspaceEnterEvent ||
            $event instanceof LogResourceReadEvent ||
            $event instanceof LogWorkspaceToolReadEvent ||
            $event instanceof LogDesktopToolReadEvent ||
            $event instanceof LogAdminToolReadEvent
        )) {
            $this->logConnectManager->manageConnection($log);
        }
    }

    public function onLogListItem(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('twig')->render(
            '@ClarolineCore/log/view_list_item_sentence.html.twig',
            ['log' => $event->getLog()]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onLogDetails(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('twig')->render(
            '@ClarolineCore/log/view_details.html.twig',
            ['log' => $event->getLog()]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function disable()
    {
        $this->enabledLog = false;
    }

    public function enable()
    {
        $this->enabledLog = true;
    }

    public function setDefaults()
    {
        $this->enabledLog = $this->ch->getParameter('platform_log_enabled');
    }

    /**
     * Checks if a tool has been opened between 2 resources opening.
     * If it is the case, the resource opening will not be labelled as a repeat even if it is the same resource.
     * The same is checked between 2 tools opening.
     *
     * @return bool
     */
    private function hasBreakingRepeatEvent(SessionInterface $session, LogGenericEvent $event)
    {
        $hasBreakingRepeatEvent = false;
        $breakingSignatures = [
            LogWorkspaceToolReadEvent::ACTION,
            LogResourceReadEvent::ACTION,
            LogDesktopToolReadEvent::ACTION,
            LogAdminToolReadEvent::ACTION,
        ];
        $breakingWorkspaceSignatures = [
            LogDesktopToolReadEvent::ACTION,
            LogAdminToolReadEvent::ACTION,
        ];

        // Only checks for tools (desktop, workspace & admin) & resources opening
        if ($event->getIsWorkspaceEnterEvent() || $event->getIsToolReadEvent() || $event->getIsResourceReadEvent()) {
            if (!is_null($session->get($event->getAction()))) {
                $oldArray = json_decode($session->get($event->getAction()));
                $oldSignature = $oldArray->logSignature;
                $oldTime = $oldArray->time;

                foreach ($breakingSignatures as $breakingSignature) {
                    if ((($event->getIsWorkspaceEnterEvent() && in_array($breakingSignature, $breakingWorkspaceSignatures)) ||
                        (!$event->getIsWorkspaceEnterEvent() && $oldSignature !== $breakingSignature)) &&
                        $session->get($breakingSignature)
                    ) {
                        $breakingArray = json_decode($session->get($breakingSignature));

                        if ($oldTime < $breakingArray->time) {
                            $hasBreakingRepeatEvent = true;
                            break;
                        }
                    }
                }
            }
        }

        return $hasBreakingRepeatEvent;
    }
}
