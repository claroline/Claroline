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
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogGroupDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogNotRepeatableInterface;
use Claroline\CoreBundle\Event\Log\LogResourceDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogUserDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceRoleDeleteEvent;
use Claroline\CoreBundle\Event\LogCreateEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Manager\Resource\ResourceNodeManager;
use Claroline\CoreBundle\Manager\RoleManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service
 */
class LogListener
{
    private $om;
    private $tokenStorage;
    private $container;
    private $roleManager;
    private $ch;
    private $resourceNodeManager;
    private $resourceEvalManager;

    /**
     * @DI\InjectParams({
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "container"           = @DI\Inject("service_container"),
     *     "roleManager"         = @DI\Inject("claroline.manager.role_manager"),
     *     "ch"                  = @DI\Inject("claroline.config.platform_config_handler"),
     *     "resourceNodeManager" = @DI\Inject("claroline.manager.resource_node"),
     *     "resourceEvalManager" = @DI\Inject("claroline.manager.resource_evaluation_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        $container,
        RoleManager $roleManager,
        PlatformConfigurationHandler $ch,
        ResourceNodeManager $resourceNodeManager,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
        $this->roleManager = $roleManager;
        $this->ch = $ch;
        $this->enabledLog = $this->ch->getParameter('platform_log_enabled');
        $this->resourceNodeManager = $resourceNodeManager;
        $this->resourceEvalManager = $resourceEvalManager;
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
                if ($this->container->isScopeActive('request')) {
                    $request = $this->container->get('request');
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
            $request = $this->container->get('request');
            $session = $request->getSession();

            $is = false;
            $pushInSession = true;
            $now = time();

            //if ($session->get($event->getAction()) != null) {
            if (null !== $session->get($event->getLogSignature())) {
                $oldArray = json_decode($session->get($event->getLogSignature()));
                $oldSignature = $oldArray->logSignature;
                $oldTime = $oldArray->time;

                if ($oldSignature === $event->getLogSignature()) {
                    $diff = ($now - $oldTime);
                    $notRepeatableLogTimeInSeconds = $this->container->getParameter(
                        'non_repeatable_log_time_in_seconds'
                    );

                    if ($event->getIsWorkspaceEnterEvent()) {
                        $notRepeatableLogTimeInSeconds = $notRepeatableLogTimeInSeconds * 3;
                    }

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

            return $is;
        } else {
            return false;
        }
    }

    /**
     * @DI\Observe("log")
     *
     * @param LogGenericEvent $event
     */
    public function onLog(LogGenericEvent $event)
    {
        $logCreated = false;

        if (!($event instanceof LogNotRepeatableInterface) || !$this->isARepeat($event)) {
            $this->createLog($event);
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
                $this->resourceNodeManager->addView($event->getResource());
            }
            if ($logCreated && !empty($user) && 'anon.' !== $user && 'directory' !== $event->getResource()->getResourceType()->getName()) {
                $this->resourceEvalManager->updateResourceUserEvaluationData(
                    $event->getResource(),
                    $user,
                    new \DateTime(),
                    AbstractResourceEvaluation::STATUS_OPENED,
                    null,
                    null,
                    null,
                    null,
                    null,
                    false,
                    false,
                    true
                );
            }
        }
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
}
