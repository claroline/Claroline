<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Subscriber;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\WorkspaceEvents;
use Claroline\CoreBundle\Event\Workspace\OpenWorkspaceEvent;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceEvaluationSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var WorkspaceEvaluationManager */
    private $manager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        WorkspaceEvaluationManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkspaceEvents::OPEN => 'open',
            EvaluationEvents::RESOURCE => 'updateEvaluation',
        ];
    }

    public function open(OpenWorkspaceEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        // Update current user evaluation
        if ($user instanceof User) {
            $this->manager->updateUserEvaluation(
                $event->getWorkspace(),
                $user,
                ['status' => AbstractEvaluation::STATUS_OPENED]
            );
        }
    }

    /**
     * Updates WorkspaceEvaluation each time a user is evaluated for a Resource.
     */
    public function updateEvaluation(ResourceEvaluationEvent $event)
    {
        $resourceUserEvaluation = $event->getEvaluation();
        $resourceNode = $resourceUserEvaluation->getResourceNode();
        $workspace = $resourceNode->getWorkspace();
        $user = $resourceUserEvaluation->getUser();

        $this->manager->computeEvaluation($workspace, $user, $resourceUserEvaluation);
    }
}
