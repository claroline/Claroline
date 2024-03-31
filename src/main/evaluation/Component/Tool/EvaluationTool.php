<?php

namespace Claroline\EvaluationBundle\Component\Tool;

use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;

class EvaluationTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'evaluation';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            DesktopContext::getName(),
            WorkspaceContext::getName(),
        ]);
    }

    public static function getIcon(): string
    {
        return 'award';
    }
}
