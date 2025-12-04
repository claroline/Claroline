<?php

namespace Claroline\CursusBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Finder\EventPresenceType;
use Claroline\CursusBundle\Repository\SessionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MyEventPresencesList extends ListSourceComponent
{
    private SessionRepository $sessionRepo;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        ObjectManager $om
    ) {
        $this->sessionRepo = $om->getRepository(Session::class);
    }

    public static function getName(): string
    {
        return 'my_event_presences';
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

    protected function getRequest(string $context, ?ContextSubjectInterface $contextSubject = null, ?bool $filterSubject = true, ?Request $request = null): FinderRequest
    {
        $finderRequest = parent::getRequest($context, $contextSubject, $filterSubject, $request);

        if ($filterSubject && $context === WorkspaceContext::getName()) {
            $workspaceSessions = array_map(function (Session $session) {
                return $session->getUuid();
            }, $this->sessionRepo->findByWorkspace($contextSubject));

            $finderRequest->addFilter('event.workspace', $workspaceSessions);
        }

        $finderRequest->addFilter('user', $this->tokenStorage->getToken()?->getUser()->getUuid());

        return $finderRequest;
    }
}
