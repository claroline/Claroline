<?php

namespace Claroline\CursusBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CursusBundle\Finder\EventPresenceType;
use Symfony\Component\HttpFoundation\Request;

class EventPresencesList extends ListSourceComponent
{
    public static function getName(): string
    {
        return 'event_presences';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            DesktopContext::getName(),
            WorkspaceContext::getName(),
        ]);
    }

    public static function getClass(): string
    {
        return EventPresenceType::class;
    }

    protected function getRequest(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderRequest
    {
        $finderRequest = parent::getRequest($context, $contextSubject, $request);

        if ($context === WorkspaceContext::getName()) {
            $finderRequest->addFilter('event.workspace', $contextSubject->getUuid());
        }

        return $finderRequest;
    }
}
