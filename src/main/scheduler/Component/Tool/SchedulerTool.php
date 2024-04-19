<?php

namespace Claroline\SchedulerBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\AdministrationContext;

class SchedulerTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'scheduler';
    }

    public static function getIcon(): string
    {
        return 'clock';
    }

    public function supportsContext(string $context): bool
    {
        return AdministrationContext::getName() === $context;
    }
}
