<?php

namespace Claroline\CoreBundle\Component\Log\Functional;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;

class LogWorkspaceCreate extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'workspace.create';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'post', Workspace::class) => ['logCreate', -25],
        ];
    }

    public function logCreate(CreateEvent $event): void
    {
        $workspace = $event->getObject();

        $this->log(
            $this->getTranslator()->trans('workspace.create_message', [
                '%workspace%' => $workspace->getName(),
            ], 'log'),
            $workspace,
        );
    }
}
