<?php

namespace Claroline\OpenBadgeBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\OpenBadgeBundle\Finder\BadgeType;
use Symfony\Component\HttpFoundation\Request;

class BadgesSource extends ListSourceComponent
{
    public static function getName(): string
    {
        return 'badges';
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
        return BadgeType::class;
    }

    protected function getQuery(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderQuery
    {
        $finderQuery = parent::getQuery($context, $contextSubject, $request);
        if (WorkspaceContext::getName() === $context) {
            $finderQuery->addFilter('badge.workspace', $contextSubject->getUuid());
        }

        return $finderQuery;
    }
}
