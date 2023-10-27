<?php

namespace Claroline\CoreBundle\Component\Tool;

use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\AdministrationContext;

class ParametersTool extends AbstractTool
{
    public static function getShortName(): string
    {
        return 'parameters';
    }

    public function supportsContext(string $context): bool
    {
        return AdministrationContext::class === $context;
    }
}
