<?php

namespace Claroline\CoreBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderRequest;
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

    protected function getRequest(string $context, ?ContextSubjectInterface $contextSubject = null, ?bool $filterSubject = true, ?Request $request = null): FinderRequest
    {
        $finderRequest = parent::getRequest($context, $contextSubject, $filterSubject, $request);

        $finderRequest->addFilter('contextName', $context);
        if ($contextSubject) {
            $finderRequest->addFilter('contextId', $contextSubject->getContextIdentifier());
        }

        // filter the tool list by current user if he is not an admin
        $context = $this->contextProvider->getContext($context);
        if (!$context->isGranted('ADMINISTRATE', $contextSubject)) {
            $roles = $context->getRoles($this->tokenStorage->getToken(), $contextSubject);
            $finderRequest->addFilter('roles', $roles);
        }

        return $finderRequest;
    }
}
