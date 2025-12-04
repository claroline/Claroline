<?php

namespace Claroline\EvaluationBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\EvaluationBundle\Finder\ResourceEvaluationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class MyResourceEvaluationsList extends ListSourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getName(): string
    {
        return 'my_resource_evaluations';
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
        return ResourceEvaluationType::class;
    }

    protected function getRequest(string $context, ?ContextSubjectInterface $contextSubject = null, ?bool $filterSubject = true, ?Request $request = null): FinderRequest
    {
        $finderRequest = parent::getRequest($context, $contextSubject, $filterSubject, $request);

        $finderRequest->addFilter('user', $this->tokenStorage->getToken()?->getUser()->getUuid());

        return $finderRequest;
    }
}
