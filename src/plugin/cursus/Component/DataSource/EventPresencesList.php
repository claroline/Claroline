<?php

namespace Claroline\CursusBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Finder\EventPresenceType;
use Claroline\CursusBundle\Repository\SessionRepository;
use Symfony\Component\HttpFoundation\Request;

class EventPresencesList extends ListSourceComponent
{
    private SessionRepository $sessionRepo;

    public function __construct(
        ObjectManager $om
    ) {
        $this->sessionRepo = $om->getRepository(Session::class);
    }

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

    protected function getQuery(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderQuery
    {
        $finderQuery = parent::getQuery($context, $contextSubject, $request);

        if ($context === WorkspaceContext::getName()) {
            $workspaceSessions = array_map(function (Session $session) {
                return $session->getUuid();
            }, $this->sessionRepo->findByWorkspace($contextSubject));

            $finderQuery->addFilter('event.workspace', $workspaceSessions);
        }

        return $finderQuery;
    }
}
