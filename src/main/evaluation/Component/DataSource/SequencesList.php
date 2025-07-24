<?php

namespace Claroline\EvaluationBundle\Component\DataSource;

use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\PublicContext;
use Claroline\CoreBundle\Controller\Workspace\WorkspaceController;
use Claroline\EvaluationBundle\Finder\SequenceType;

final class SequencesList extends ListSourceComponent
{
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
}
