<?php

namespace Claroline\MessageBundle\Component\Tool;

use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\DesktopContext;

class MessagesTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'messaging';
    }

    public static function getIcon(): string
    {
        return 'envelope';
    }

    public function supportsContext(string $context): bool
    {
        return DesktopContext::getName() === $context;
    }
}
