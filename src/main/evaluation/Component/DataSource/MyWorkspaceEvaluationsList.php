<?php

namespace Claroline\EvaluationBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\EvaluationBundle\Finder\WorkspaceEvaluationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class MyWorkspaceEvaluationsList extends ListSourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getName(): string
    {
        return 'my_workspace_evaluations';
    }

    public function supportsContext(string $context): bool
    {
        return $context === DesktopContext::getName();
    }

    public static function getClass(): string
    {
        return WorkspaceEvaluationType::class;
    }

    protected function getQuery(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderQuery
    {
        $finderQuery = parent::getQuery($context, $contextSubject, $request);

        $finderQuery->addFilter('user', $this->tokenStorage->getToken()?->getUser()->getUuid());

        return $finderQuery;
    }
}
