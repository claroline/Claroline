<?php

namespace Claroline\EvaluationBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\EvaluationBundle\Finder\SequenceEvaluationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MySequenceEvaluationsList extends ListSourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getName(): string
    {
        return 'my_sequence_evaluations';
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
        return SequenceEvaluationType::class;
    }

    protected function getQuery(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderQuery
    {
        $finderQuery = parent::getQuery($context, $contextSubject, $request);

        if ($contextSubject) {
            $finderQuery->addFilter('sequence.workspace', $contextSubject->getContextIdentifier());
        }
        $finderQuery->addFilter('user', $this->tokenStorage->getToken()?->getUser()->getUuid());

        return $finderQuery;
    }
}
