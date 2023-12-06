<?php

namespace Claroline\CoreBundle\Component\Log\Functional;

use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\ContextEvents;
use Claroline\CoreBundle\Event\Context\OpenContextEvent;
use Claroline\LogBundle\Component\Log\AbstractFunctionalLog;

class LogWorkspaceOpen extends AbstractFunctionalLog
{
    public static function getName(): string
    {
        return 'workspace.open';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContextEvents::OPEN => ['logOpen', -25],
        ];
    }

    public function logOpen(OpenContextEvent $openEvent): void
    {
        if (WorkspaceContext::getName() !== $openEvent->getContextType()) {
            return;
        }

        /** @var Workspace $openedWorkspace */
        $openedWorkspace = $openEvent->getContextSubject();

        $this->log(
            $this->getTranslator()->trans('workspace.open_message', [
                '%workspace%' => $openedWorkspace->getName(),
            ], 'log'),
            $openedWorkspace
        );
    }
}
