<?php

namespace Claroline\EvaluationBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\PublicContext;
use Claroline\CoreBundle\Controller\Workspace\WorkspaceController;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\EvaluationBundle\Finder\SequenceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class SequencesList extends ListSourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ToolManager $toolManager,
    ) {
    }

    public static function getName(): string
    {
        return 'sequences';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            PublicContext::getName(),
            DesktopContext::getName(),
            WorkspaceController::getName(),
        ]);
    }

    public static function getClass(): string
    {
        return SequenceType::class;
    }

    protected function getQuery(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderQuery
    {
        $finderQuery = parent::getQuery($context, $contextSubject, $request);

        $roles = $this->tokenStorage->getToken()?->getRoleNames();
        if (!in_array(PlatformRoles::ADMIN, $roles) && !$this->toolManager->isGranted('FOLLOW', 'progression', $contextSubject?->getContextIdentifier())) {
            $finderQuery->addFilter('roles', $roles);
        }

        if (!$this->toolManager->isGranted('EDIT', 'progression', $contextSubject?->getContextIdentifier())) {
            $finderQuery->addFilter('published', true);
        }

        return $finderQuery;
    }
}
