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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\CatalogEvents\WorkspaceEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Event\Workspace\OpenWorkspaceEvent;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\InitializeWorkspaceEvaluations;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceEvaluationSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var WorkspaceEvaluationManager */
    private $manager;

    /** @var WorkspaceRepository */
    private $workspaceRepo;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        MessageBusInterface $messageBus,
        ObjectManager $om,
        WorkspaceEvaluationManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->messageBus = $messageBus;
        $this->manager = $manager;

        $this->workspaceRepo = $om->getRepository(Workspace::class);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::ADD_ROLE => 'initializeEvaluations',
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
     * Initializes evaluations for newly registered users.
     */
    public function initializeEvaluations(AddRoleEvent $event)
    {
        $role = $event->getRole();

        // init evaluation for all the workspaces accessible by the role
        // this is not required by the code, but is a feature for managers to see users in evaluation tool/exports
        // event if they have not opened the workspace yet.
        $workspaces = $this->workspaceRepo->findByRoles([$role->getName()]);
        foreach ($workspaces as $workspace) {
            $this->messageBus->dispatch(new InitializeWorkspaceEvaluations($workspace, $event->getUsers()));
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
