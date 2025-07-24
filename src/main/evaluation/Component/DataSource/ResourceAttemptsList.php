<?php

namespace Claroline\EvaluationBundle\Component\DataSource;

use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\EvaluationBundle\Finder\ResourceAttemptType;

final class ResourceAttemptsList extends ListSourceComponent
{
    public static function getName(): string
    {
        return 'resource_attempts';
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
        return ResourceAttemptType::class;
    }
}
