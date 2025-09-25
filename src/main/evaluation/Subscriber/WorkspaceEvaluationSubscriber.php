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

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\SequenceEvaluationEvent;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\InitializeWorkspaceEvaluations;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Updates the WorkspaceEvaluation in response to application events.
 */
class WorkspaceEvaluationSubscriber implements EventSubscriberInterface
{
    private WorkspaceRepository $workspaceRepo;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageBusInterface $messageBus,
        ObjectManager $om,
        private readonly WorkspaceEvaluationManager $manager
    ) {
        $this->workspaceRepo = $om->getRepository(Workspace::class);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::ADD_ROLE => 'onAddRole',
            EvaluationEvents::SEQUENCE_EVALUATION => 'onSequenceEvaluate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Sequence::class) => 'onSequencePublicationChange',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, Sequence::class) => 'onSequenceDelete',
        ];
    }

    /**
     * Initializes evaluations for newly registered users.
     */
    public function onAddRole(AddRoleEvent $event): void
    {
        $role = $event->getRole();

        if (!$this->tokenStorage->getToken()?->getUser() instanceof User) {
            return;
        }

        // init evaluation for all the workspaces accessible by the role
        // this is not required by the code, but is a feature for managers to see users in evaluation tool/exports
        // event if they have not opened the workspace yet.
        $workspaces = $this->workspaceRepo->findByRoles([$role->getName()]);
        foreach ($workspaces as $workspace) {
            $this->messageBus->dispatch(
                new InitializeWorkspaceEvaluations($workspace->getId(), array_map(function (User $user) {
                    return $user->getId();
                }, $event->getUsers())), [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]
            );
        }
    }

    /**
     * Updates WorkspaceEvaluation each time a user is evaluated for a Resource.
     */
    public function onSequenceEvaluate(SequenceEvaluationEvent $event): void
    {
        $sequenceEvaluation = $event->getEvaluation();

        $user = $sequenceEvaluation->getUser();
        $sequence = $sequenceEvaluation->getSequence();
        $workspace = $sequence->getWorkspace();

        if ($this->manager->isEvaluated($workspace)) {
            $evaluation = $this->manager->getUserEvaluation($workspace, $user, true);
            $this->manager->updateUserEvaluation($evaluation, $sequenceEvaluation);
        }
    }

    /**
     * Recomputes WorkspaceEvaluations when a resource is deleted.
     */
    public function onSequenceDelete(DeleteEvent $event): void
    {
        /** @var Sequence $sequence */
        $sequence = $event->getObject();

        $this->manager->recomputeEvaluations($sequence->getWorkspace());
    }

    public function onSequencePublicationChange(UpdateEvent $event): void
    {
        /** @var Sequence $sequence */
        $sequence = $event->getObject();
        $oldData = $event->getOldData();

        if (!empty($oldData['meta']) && ($oldData['meta']['published'] !== $sequence->isPublished())) {
            $this->manager->recomputeEvaluations($sequence->getWorkspace());
        }
    }
}
