<?php

namespace Claroline\CoreBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Finder\WorkspaceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * List the workspaces in which the current user is registered.
 */
final class MyWorkspacesList extends ListSourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public static function getName(): string
    {
        return 'my_workspaces';
    }

    public static function getClass(): string
    {
        return WorkspaceType::class;
    }

    public function supportsContext(string $context): bool
    {
        return $context === DesktopContext::getName();
    }

    protected function getRequest(string $context, ?ContextSubjectInterface $contextSubject = null, ?bool $filterSubject = true, ?Request $request = null): FinderRequest
    {
        $finderRequest = parent::getRequest($context, $contextSubject, $filterSubject, $request);

        $finderRequest->addFilter('roles', $this->tokenStorage->getToken()->getRoleNames());

        return $finderRequest;
    }
}
