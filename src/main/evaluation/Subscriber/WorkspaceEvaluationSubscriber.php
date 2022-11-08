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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
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
    /** @var ObjectManager */
    private $om;
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
        $this->om = $om;
        $this->manager = $manager;

        $this->workspaceRepo = $om->getRepository(Workspace::class);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkspaceEvents::OPEN => 'onOpen',
            SecurityEvents::ADD_ROLE => 'onAddRole',
            EvaluationEvents::RESOURCE => 'onResourceEvaluate',
            Crud::getEventName('update', 'post', ResourceNode::class) => 'onResourceUpdate',
            Crud::getEventName('delete', 'post', ResourceNode::class) => 'onResourceDelete',
        ];
    }

    /**
     * Updates the workspace evaluation status to "opened".
     */
    public function onOpen(OpenWorkspaceEvent $event)
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
    public function onAddRole(AddRoleEvent $event)
    {
        $role = $event->getRole();

        // init evaluation for all the workspaces accessible by the role
        // this is not required by the code, but is a feature for managers to see users in evaluation tool/exports
        // event if they have not opened the workspace yet.
        $workspaces = $this->workspaceRepo->findByRoles([$role->getName()]);
        foreach ($workspaces as $workspace) {
            $this->messageBus->dispatch(
                new InitializeWorkspaceEvaluations($workspace->getId(), array_map(function (User $user) {
                    return $user->getId();
                }, $event->getUsers()))
            );
        }
    }

    /**
     * Updates WorkspaceEvaluation each time a user is evaluated for a Resource.
     */
    public function onResourceEvaluate(ResourceEvaluationEvent $event)
    {
        $resourceUserEvaluation = $event->getEvaluation();
        $resourceNode = $resourceUserEvaluation->getResourceNode();
        $workspace = $resourceNode->getWorkspace();
        $user = $resourceUserEvaluation->getUser();

        $this->manager->computeEvaluation($workspace, $user, $resourceUserEvaluation);
    }

    /**
     * Recomputes WorkspaceEvaluations when a resource is deleted.
     */
    public function onResourceDelete(DeleteEvent $event)
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $event->getObject();
        $workspace = $resourceNode->getWorkspace();

        // update workspace estimated duration when needed
        if ($resourceNode->isRequired() && $resourceNode->isPublished() && $resourceNode->getEstimatedDuration()) {
            $workspace->setEstimatedDuration(
                $workspace->getEstimatedDuration() ?? 0 - $resourceNode->getEstimatedDuration()
            );

            $this->om->persist($workspace);
            $this->om->flush();
        }

        $this->manager->recompute($resourceNode->getWorkspace());
    }

    public function onResourceUpdate(UpdateEvent $event)
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $event->getObject();
        $workspace = $resourceNode->getWorkspace();

        // Workspace estimated duration is the sum of the estimated duration of all the published required resources
        // So we need to update the workspace duration :
        //   - Each time a resource is published / unpublished
        //   - Each time a resource is set as required / optional
        //   - Each time the estimated duration of a resource is updated
        $updateDuration = false;

        // We compute the duration diff we will need to add / subtract to the workspace estimated duration
        // We do it to avoid recomputing based on all resources estimated duration (it would be costly and would not work in a flush suite)
        $oldDuration = $event->getOldData('evaluation.estimatedDuration') ?? 0;
        $currentDuration = $resourceNode->getEstimatedDuration() ?? 0;
        if ($event->hasPropertyChanged('evaluation.estimatedDuration', 'getEstimatedDuration')) {
            $diffDuration = $currentDuration - $oldDuration;
            $updateDuration = true;
        } else {
            $diffDuration = $currentDuration;
        }

        if ($event->hasPropertyChanged('evaluation.required', 'isRequired')
            || $event->hasPropertyChanged('meta.published', 'isPublished')) {
            // there are changes in the published / required resources of the workspace
            // we will need to update the estimated duration of the workspace accordingly
            $updateDuration = true;
        }

        if ($updateDuration) {
            if ($resourceNode->isRequired() && $resourceNode->isPublished()) {
                $workspace->setEstimatedDuration(
                    $workspace->getEstimatedDuration() ?? 0 + $diffDuration
                );
            } else {
                $workspace->setEstimatedDuration(
                    $workspace->getEstimatedDuration() ?? 0 - $diffDuration
                );
            }

            $this->om->persist($workspace);
            $this->om->flush();
        }

        if ($resourceNode->isRequired() && $event->hasPropertyChanged('meta.published', 'isPublished')) {
            $this->manager->recompute($resourceNode->getWorkspace());
        }
    }
}
