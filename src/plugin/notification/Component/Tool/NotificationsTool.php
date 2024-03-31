<?php

namespace Icap\NotificationBundle\Component\Tool;

use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\AccountContext;

class NotificationsTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'notifications';
    }

    public static function getIcon(): string
    {
        return 'bell';
    }

    public function supportsContext(string $context): bool
    {
        return AccountContext::getName() === $context;
    }
}
