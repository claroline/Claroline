<?php

namespace Claroline\CoreBundle\Component\Tool;

use Claroline\AppBundle\Component\Tool\AbstractToolComponent;
use Claroline\CoreBundle\Component\Context\AdministrationContext;

class ParametersTool extends AbstractToolComponent
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
