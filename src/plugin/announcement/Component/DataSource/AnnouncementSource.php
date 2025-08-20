<?php

namespace Claroline\AnnouncementBundle\Component\DataSource;

use Claroline\AnnouncementBundle\Finder\AnnouncementType;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\PublicContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AnnouncementSource extends ListSourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getName(): string
    {
        return 'announcements';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            PublicContext::getName(),
            DesktopContext::getName(),
            WorkspaceContext::getName(),
        ]);
    }

    public static function getClass(): string
    {
        return AnnouncementType::class;
    }

    protected function getQuery(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderQuery
    {
        $finderQuery = parent::getQuery($context, $contextSubject, $request);

        if (PublicContext::getName() === $context) {
            // only announcements accessible by anonymous users
            $roles = [PlatformRoles::ANONYMOUS];
        } else {
            // filter by current user roles
            $roles = $this->tokenStorage->getToken()?->getRoleNames() ?? [PlatformRoles::ANONYMOUS];
        }

        if (WorkspaceContext::getName() === $context) {
            $finderQuery->addFilter('workspace', $contextSubject->getUuid());
        }

        $finderQuery->addFilter('roles', $roles);

        return $finderQuery;
    }
}
