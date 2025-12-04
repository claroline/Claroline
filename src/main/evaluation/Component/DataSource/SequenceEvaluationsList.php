<?php

namespace Claroline\EvaluationBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderRequest;
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

    protected function getRequest(string $context, ?ContextSubjectInterface $contextSubject = null, ?bool $filterSubject = true, ?Request $request = null): FinderRequest
    {
        $finderRequest = parent::getRequest($context, $contextSubject, $filterSubject, $request);

        if ($filterSubject && $contextSubject) {
            $finderRequest->addFilter('sequence.workspace', $contextSubject->getContextIdentifier());
        }

        return $finderRequest;
    }
}
