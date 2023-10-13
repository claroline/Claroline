<?php

namespace Claroline\CoreBundle\Component\Tool;

use Claroline\AppBundle\Component\Tool\AbstractToolComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;

class ResourcesTool extends AbstractToolComponent
{

    public static function getShortName(): string
    {
        return 'resources';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            DesktopContext::class,
            WorkspaceContext::class,
        ]);
    }
}
