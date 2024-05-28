<?php

namespace Claroline\CoreBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\AdministrationContext;

class IntegrationTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'integration';
    }

    public static function getIcon(): string
    {
        return 'plug';
    }

    public function isRequired(string $context, ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }

    public function supportsContext(string $context): bool
    {
        return AdministrationContext::getName() === $context;
    }
}
