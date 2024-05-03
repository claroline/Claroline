<?php

namespace Claroline\CursusBundle\Component\Tool;

use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\PublicContext;

class PresenceTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'presence';
    }

    public static function getIcon(): string
    {
        return 'signature';
    }

    public function supportsContext(string $context): bool
    {
        return PublicContext::getName() === $context;
    }
}
