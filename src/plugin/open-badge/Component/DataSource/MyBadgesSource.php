<?php

namespace Claroline\OpenBadgeBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\OpenBadgeBundle\Finder\AssertionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MyBadgesSource extends ListSourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getName(): string
    {
        return 'my_badges';
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
        return AssertionType::class;
    }

    protected function getQuery(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderQuery
    {
        $finderQuery = parent::getQuery($context, $contextSubject, $request);

        $user = $this->tokenStorage->getToken()?->getUser();

        $finderQuery->addFilter('recipient', $user?->getUuid());
        if (WorkspaceContext::getName() === $context) {
            $finderQuery->addFilter('badge.workspace', $contextSubject->getUuid());
        }

        return $finderQuery;
    }
}
