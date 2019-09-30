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
use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Event\Log\LogAdminToolReadEvent;
use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Event\Log\LogDesktopToolReadEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogGroupDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogNotRepeatableInterface;
use Claroline\CoreBundle\Event\Log\LogResourceDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogUserDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceRoleDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Event\LogCreateEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\LogConnectManager;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogListener
{
    private $om;
    private $tokenStorage;
    private $container;
    private $roleManager;
    private $ch;
    private $enabledLog;
    private $resourceManager;
    private $resourceEvalManager;
    private $logConnectManager;

    /**
     * @param ObjectManager                $om
     * @param TokenStorageInterface        $tokenStorage
     * @param ContainerInterface           $container
     * @param RoleManager                  $roleManager
     * @param PlatformConfigurationHandler $ch
     * @param ResourceManager              $resourceManager
     * @param ResourceEvaluationManager    $resourceEvalManager
     * @param LogConnectManager            $logConnectManager
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        ContainerInterface $container,
        RoleManager $roleManager,
        PlatformConfigurationHandler $ch,
        ResourceManager $resourceManager,
        ResourceEvaluationManager $resourceEvalManager,
        LogConnectManager $logConnectManager
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
        $this->roleManager = $roleManager;
        $this->ch = $ch;
        $this->enabledLog = $this->ch->getParameter('platform_log_enabled');
        $this->resourceManager = $resourceManager;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->logConnectManager = $logConnectManager;
    }

    private function createLog(LogGenericEvent $event)
    {
        if (!$this->enabledLog) {
            return;
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
                $doerType = Log::doerTypePlatform;
            } else {
                if ('anon.' === $token->getUser()) {
                    $doer = null;
                    $doerType = Log::doerTypeAnonymous;
                } else {
                    $doer = $token->getUser();
                    $doerType = Log::doerTypeUser;
                }
                if ($request = $this->container->get('request_stack')->getMasterRequest()) {
                    $doerSessionId = $request->getSession()->getId();
                    $doerIp = $request->getClientIp();
                } else {
                    $doerIp = 'CLI';
                }
            }
        } elseif (LogGenericEvent::PLATFORM_EVENT_TYPE === $event->getDoer()) {
            $doer = null;
            $doerType = Log::doerTypePlatform;
        } else {
            $doer = $event->getDoer();
            $doerType = Log::doerTypeUser;
        }

        $log = new Log();

        //Simple type properties
        $log
            ->setAction($event->getAction())
            ->setToolName($event->getToolName())
            ->setIsDisplayedInAdmin($event->getIsDisplayedInAdmin())
            ->setIsDisplayedInWorkspace($event->getIsDisplayedInWorkspace())
            ->setOtherElementId($event->getOtherElementId());

        //Object properties
        $log->setOwner($event->getOwner());
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
            if ($receiverGroup = $event->getReceiverGroup()) {
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

        if (null !== $doer) {
            $platformRoles = $this->roleManager->getPlatformRoles($doer);

            foreach ($platformRoles as $platformRole) {
                $log->addDoerPlatformRole($platformRole);
            }

            if (null !== $event->getWorkspace()) {
                $workspaceRoles = $this->roleManager->getWorkspaceRolesForUser($doer, $event->getWorkspace());

                foreach ($workspaceRoles as $workspaceRole) {
                    $log->addDoerWorkspaceRole($workspaceRole);
                }
            }
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
                'publicUrl' => $doer->getPublicUrl(),
            ];

            if (count($log->getDoerPlatformRoles()) > 0) {
                $doerPlatformRolesDetails = [];
                foreach ($log->getDoerPlatformRoles() as $platformRole) {
                    $doerPlatformRolesDetails[] = $platformRole->getTranslationKey();
                }
                $details['doer']['platformRoles'] = $doerPlatformRolesDetails;
            }
            if (count($log->getDoerWorkspaceRoles()) > 0) {
                $doerWorkspaceRolesDetails = [];
                foreach ($log->getDoerWorkspaceRoles() as $workspaceRole) {
                    $doerWorkspaceRolesDetails[] = $workspaceRole->getTranslationKey();
                }
                $details['doer']['workspaceRoles'] = $doerWorkspaceRolesDetails;
            }
        }
        $log->setDetails($details);

        $this->om->persist($log);
        $this->om->flush();

        $createLogEvent = new LogCreateEvent($log);
        $this->container->get('event_dispatcher')->dispatch(LogCreateEvent::NAME, $createLogEvent);

        return $log;
    }

    /**
     * Is a repeat if the session contains a same logSignature for the same action category.
     */
    public function isARepeat(LogGenericEvent $event)
    {
        if (null === $this->tokenStorage->getToken()) {
            //Only if have a user session;
            return false;
        }

        if ($event instanceof LogNotRepeatableInterface) {
            $request = $this->container->get('request_stack')->getMasterRequest();
            $session = $request->getSession();

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

    /**
     * @param LogGenericEvent $event
     */
    public function onLog(LogGenericEvent $event)
    {
        $log = null;
        $logCreated = false;

        if (!($event instanceof LogNotRepeatableInterface) || !$this->isARepeat($event)) {
            $log = $this->createLog($event);
            $logCreated = true;
        }

        // Increment view count
        if ($event instanceof LogResourceReadEvent) {
            // Get connected user
            $tokenStorage = $this->container->get('security.token_storage');
            $token = $tokenStorage->getToken();
            $user = $token ? $token->getUser() : null;

            // Increment view count if viewer is not creator of the resource
            if (is_null($user) || is_string($user) || $user !== $event->getResource()->getCreator()) {
                // TODO : add me in an event on the resource 'open'
                $this->resourceManager->addView($event->getResource());
            }
            if ($logCreated && !empty($user) && 'anon.' !== $user && 'directory' !== $event->getResource()->getResourceType()->getName()) {
                $this->resourceEvalManager->updateResourceUserEvaluationData(
                    $event->getResource(),
                    $user,
                    new \DateTime(),
                    ['status' => AbstractResourceEvaluation::STATUS_OPENED],
                    ['status' => true],
                    false
                );
            }
        }
        if ($logCreated && $log && (
            $event instanceof LogUserLoginEvent ||
            $event instanceof LogWorkspaceEnterEvent ||
            $event instanceof LogResourceReadEvent ||
            $event instanceof LogWorkspaceToolReadEvent ||
            $event instanceof LogDesktopToolReadEvent ||
            $event instanceof LogAdminToolReadEvent
        )) {
            $this->logConnectManager->manageConnection($log);
        }
    }

    /**
     * @param LogCreateDelegateViewEvent $event
     */
    public function onLogListItem(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:log:view_list_item_sentence.html.twig',
            ['log' => $event->getLog()]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @param LogCreateDelegateViewEvent $event
     */
    public function onLogDetails(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:log:view_details.html.twig',
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
     * @param SessionInterface $session
     * @param LogGenericEvent  $event
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
