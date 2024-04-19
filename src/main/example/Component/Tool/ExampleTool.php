<?php

namespace Claroline\ExampleBundle\Component\Tool;

use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\DesktopContext;

class ExampleTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'example';
    }

    public static function getIcon(): string
    {
        return 'tools';
    }

    public function supportsContext(string $context): bool
    {
        return DesktopContext::getName() === $context;
    }
}
