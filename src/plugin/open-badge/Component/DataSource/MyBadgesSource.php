<?php

namespace Claroline\OpenBadgeBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\OpenBadgeBundle\Finder\AssertionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MyBadgesSource extends ListSourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getName(): string
    {
        return 'my_badges';
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
        return AssertionType::class;
    }

    protected function getRequest(string $context, ?ContextSubjectInterface $contextSubject = null, ?bool $filterSubject = true, ?Request $request = null): FinderRequest
    {
        $finderRequest = parent::getRequest($context, $contextSubject, $filterSubject, $request);

        $user = $this->tokenStorage->getToken()?->getUser();

        $finderRequest->addFilter('recipient', $user?->getUuid());
        if ($filterSubject && WorkspaceContext::getName() === $context) {
            $finderRequest->addFilter('badge.workspace', $contextSubject->getUuid());
        }

        return $finderRequest;
    }
}
