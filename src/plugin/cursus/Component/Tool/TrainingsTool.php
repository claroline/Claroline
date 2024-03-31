<?php

namespace Claroline\CursusBundle\Component\Tool;

use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\DesktopContext;

class TrainingsTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'trainings';
    }

    public static function getIcon(): string
    {
        return 'graduation-cap';
    }

    public function supportsContext(string $context): bool
    {
        return DesktopContext::getName() === $context;
    }
}
