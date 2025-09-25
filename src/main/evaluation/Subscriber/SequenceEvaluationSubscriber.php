<?php

namespace Claroline\EvaluationBundle\Subscriber;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use Claroline\EvaluationBundle\Manager\SequenceEvaluationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Updates the sequence evaluation in response to user actions.
 */
class SequenceEvaluationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SequenceEvaluationManager $evaluationManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::RESOURCE_EVALUATION => 'onResourceEvaluate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, ResourceNode::class) => 'onResourcePublicationChange',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, ResourceNode::class) => 'onResourceDelete',
        ];
    }

    /**
     * Recomputes the sequence evaluations each time a user is evaluated for a resource.
     */
    public function onResourceEvaluate(ResourceEvaluationEvent $event): void
    {
        $user = $event->getUser();
        $resourceNode = $event->getResourceNode();
        $resourceEvaluation = $event->getEvaluation();

        // find sequences which use the resource and recompute the user evaluations for them
        $sequences = $this->evaluationManager->getByResourceAndUser($resourceNode, $user);
        foreach ($sequences as $sequence) {
            $evaluation = $this->evaluationManager->getUserEvaluation($sequence, $user);

            $steps = [];
            // retrieve the steps using this resource
            foreach ($sequence->getSteps() as $step) {
                if ($step->isRequired() && $step->getResource() === $resourceNode) {
                    $steps[] = $step;
                }
            }

            foreach ($steps as $step) {
                $this->evaluationManager->updateUserEvaluation($evaluation, $step, $resourceEvaluation);
            }
        }
    }

    /**
     * Recomputes SequenceEvaluations when a resource is deleted.
     */
    public function onResourceDelete(DeleteEvent $event): void
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $event->getObject();

        if ($resourceNode->isPublished()) {
            // find sequences which use the resource and recompute the user evaluations for them
            $sequences = $this->evaluationManager->getByResource($resourceNode);
            foreach ($sequences as $sequence) {
                $this->evaluationManager->recomputeEvaluations($sequence);
            }
        }
    }

    public function onResourcePublicationChange(UpdateEvent $event): void
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $event->getObject();
        $oldData = $event->getOldData();

        if (!empty($oldData['meta']) && ($oldData['meta']['published'] !== $resourceNode->isPublished())) {
            // find sequences which use the resource and recompute the user evaluations for them
            $sequences = $this->evaluationManager->getByResource($resourceNode);
            foreach ($sequences as $sequence) {
                $this->evaluationManager->recomputeEvaluations($sequence);
            }
        }
    }
}
