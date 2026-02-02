<?php

namespace Claroline\EvaluationBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Entity\Parameters\WorkspaceParameters;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkspaceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly Crud $crud
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Workspace::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Workspace::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_COPY, Workspace::class) => 'postCopy',
        ];
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();

        $data = $event->getData();
        if (array_key_exists('evaluation', $data)) {
            $this->manageEvaluationParameters($workspace, $data['evaluation']);
        }
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();

        $data = $event->getData();
        if (array_key_exists('evaluation', $data)) {
            $this->manageEvaluationParameters($workspace, $data['evaluation']);
        }
    }

    public function postCopy(CopyEvent $event): void
    {
        $options = $event->getOptions();
        /** @var Workspace $originalWorkspace */
        $originalWorkspace = $event->getObject();
        /** @var Workspace $newWorkspace */
        $newWorkspace = $event->getCopy();

        $this->om->startFlushSuite();

        /** @var WorkspaceParameters $parameters */
        $parameters = $this->om->getRepository(WorkspaceParameters::class)->findOneBy(['resource' => $originalWorkspace]);
        if ($parameters) {
            $newParameters = $this->crud->copy($parameters, $options);
            $newParameters->setWorkspace($newWorkspace);
        }

        $this->om->endFlushSuite();
    }

    private function manageEvaluationParameters(Workspace $workspace, ?array $evaluationParameters = null): void
    {
        $parameters = $this->om->getRepository(WorkspaceParameters::class)->findOneBy(['workspace' => $workspace]);
        if (empty($evaluationParameters)) {
            $this->crud->delete($parameters);
        } else {
            if ($parameters) {
                $this->crud->update($parameters, $evaluationParameters);
            } else {
                $parameters = new WorkspaceParameters();
                $parameters->setWorkspace($workspace);
                $this->crud->create($parameters, $evaluationParameters);
            }
        }
    }
}
