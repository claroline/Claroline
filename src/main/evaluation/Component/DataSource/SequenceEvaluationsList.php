<?php

namespace Claroline\EvaluationBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\EvaluationBundle\Finder\SequenceEvaluationType;
use Symfony\Component\HttpFoundation\Request;

final class SequenceEvaluationsList extends ListSourceComponent
{
    public static function getName(): string
    {
        return 'sequence_evaluations';
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

    public function getQuery(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderQuery
    {
        $finderQuery = parent::getQuery($context, $contextSubject, $request);

        if ($contextSubject) {
            $finderQuery->addFilter('sequence.workspace', $contextSubject->getContextIdentifier());
        }

        return $finderQuery;
    }
}
