<?php

namespace Claroline\CursusBundle\Component\Tool;

use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\PublicContext;

class CatalogTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'catalog';
    }

    public static function getIcon(): string
    {
        return 'graduation-cap';
    }

    public function supportsContext(string $context): bool
    {
        return PublicContext::getName() === $context;
    }
}
