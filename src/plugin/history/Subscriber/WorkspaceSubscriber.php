<?php

namespace Claroline\HistoryBundle\Subscriber;

use Claroline\AppBundle\Component\Context\AbstractContextSubscriber;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Event\Context\OpenContextEvent;
use Claroline\HistoryBundle\Manager\HistoryManager;

class WorkspaceSubscriber extends AbstractContextSubscriber
{
    public function __construct(
        private readonly SecurityManager $securityManager,
        private readonly HistoryManager $manager
    ) {
    }

    protected static function supportsContext(string $context, ?string $contextId): bool
    {
        return WorkspaceContext::getName() === $context;
    }

    protected function onOpen(OpenContextEvent $event): void
    {
        if (!$this->securityManager->isAnonymous() && !$this->securityManager->isImpersonated()) {
            $this->manager->addWorkspace($event->getContextSubject(), $this->securityManager->getCurrentUser());
        }
    }
}
