<?php

namespace Claroline\CoreBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\PublicContext;
use Claroline\CoreBundle\Controller\Workspace\WorkspaceController;
use Claroline\CoreBundle\Finder\ResourceNodeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ResourcesList extends ListSourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getName(): string
    {
        return 'resources';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            PublicContext::getName(),
            DesktopContext::getName(),
            WorkspaceController::getName(),
        ]);
    }

    protected function getQuery(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderQuery
    {
        $finderQuery = $this->parseRequest($request);

        if (!$finderQuery->hasFilter('parent') && $contextSubject) {
            // we don't want the current workspace as filter if we already filter by a parent directory
            // this permits listing resources from another workspace
            $finderQuery->addFilter('workspace', $contextSubject->getContextIdentifier());
        }

        if (PublicContext::getName() === $context) {
            $finderQuery->addFilter('workspace.public', true);
            $finderQuery->addFilter('public', true);
        } else {
            $roleNames = $this->tokenStorage->getToken()->getRoleNames();
            $finderQuery->addFilter('roles', $roleNames);
        }

        return $finderQuery;
    }

    public static function getClass(): string
    {
        return ResourceNodeType::class;
    }
}
