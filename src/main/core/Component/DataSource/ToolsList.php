<?php

namespace Claroline\CoreBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Finder\ToolType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ToolsList extends ListSourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ContextProvider $contextProvider,
    ) {
    }

    public static function getName(): string
    {
        return 'tools';
    }

    public function supportsContext(string $context): bool
    {
        return true;
    }

    public static function getClass(): string
    {
        return ToolType::class;
    }

    protected function getQuery(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderQuery
    {
        $finderQuery = parent::getQuery($context, $contextSubject, $request);

        $finderQuery->addFilter('contextName', $context);
        if ($contextSubject) {
            $finderQuery->addFilter('contextId', $contextSubject->getContextIdentifier());
        }

        // filter the tool list by current user if he is not an admin
        $context = $this->contextProvider->getContext($context);
        if (!$context->isGranted('ADMINISTRATE', $contextSubject)) {
            $roles = $context->getRoles($this->tokenStorage->getToken(), $contextSubject);
            $finderQuery->addFilter('roles', $roles);
        }

        return $finderQuery;
    }
}
