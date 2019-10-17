<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Workspace;

use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogRoleSubscribeEvent;
use Claroline\CoreBundle\Event\Log\LogRoleUnsubscribeEvent;
use Claroline\CoreBundle\Event\Resource\ResourceEvaluationEvent;
use Claroline\CoreBundle\Manager\Workspace\EvaluationManager;

class EvaluationListener
{
    /** @var EvaluationManager */
    private $evaluationManager;

    /**
     * EvaluationListener constructor.
     *
     * @param EvaluationManager $evaluationManager
     */
    public function __construct(EvaluationManager $evaluationManager)
    {
        $this->evaluationManager = $evaluationManager;
    }

    /**
     * @param ResourceEvaluationEvent $event
     */
    public function onResourceEvaluation(ResourceEvaluationEvent $event)
    {
        $resourceUserEvaluation = $event->getEvaluation();
        $resourceNode = $resourceUserEvaluation->getResourceNode();
        $workspace = $resourceNode->getWorkspace();
        $user = $resourceUserEvaluation->getUser();

        $this->evaluationManager->computeEvaluation($workspace, $user, $resourceUserEvaluation);
    }

    /**
     * @param LogGenericEvent $event
     */
    public function onLog(LogGenericEvent $event)
    {
        if ($event instanceof LogRoleSubscribeEvent || $event instanceof LogRoleUnsubscribeEvent) {
            $role = $event->getRole();
            $users = [];

            switch ($event->getAction()) {
                case LogRoleSubscribeEvent::ACTION_WORKSPACE_USER:
                case LogRoleUnsubscribeEvent::ACTION_USER:
                    $users[] = $event->getReceiver();
                    break;
                case LogRoleSubscribeEvent::ACTION_WORKSPACE_GROUP:
                case LogRoleUnsubscribeEvent::ACTION_GROUP:
                    $group = $event->getReceiverGroup();
                    $users = $group->getUsers()->toArray();
                    break;
            }
            $this->evaluationManager->manageRoleSubscription(
                $role,
                $users,
                $event instanceof LogRoleSubscribeEvent ? 'add' : 'remove'
            );
        }
    }

    /**
     * @param PatchEvent $event
     */
    public function groupUsersPostCollectionPatch(PatchEvent $event)
    {
        if ('user' === $event->getProperty()) {
            switch ($event->getAction()) {
                case 'add':
                case 'remove':
                    $this->evaluationManager->manageGroupSubscription($event->getObject(), $event->getValue(), $event->getAction());
                    break;
            }
        }
    }
}
