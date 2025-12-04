<?php

namespace Claroline\CoreBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\PublicContext;
use Claroline\CoreBundle\Finder\WorkspaceType;
use Symfony\Component\HttpFoundation\Request;

/**
 * List all the workspaces (excluding models) visible by the current user.
 */
final class WorkspacesList extends ListSourceComponent
{
    public static function getName(): string
    {
        return 'workspaces';
    }

    public static function getClass(): string
    {
        return WorkspaceType::class;
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            PublicContext::getName(),
            DesktopContext::getName(),
        ]);
    }

    protected function getRequest(string $context, ?ContextSubjectInterface $contextSubject = null, ?bool $filterSubject = true, ?Request $request = null): FinderRequest
    {
        $finderRequest = parent::getRequest($context, $contextSubject, $filterSubject, $request);

        if (PublicContext::getName() === $context) {
            $finderRequest->addFilter('model', false);
            $finderRequest->addFilter('personal', false);
            $finderRequest->addFilter('public', true);
        }

        return $finderRequest;
    }
}
