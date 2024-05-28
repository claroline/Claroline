<?php

namespace Claroline\CoreBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\AdministrationContext;

class TemplatesTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'templates';
    }

    public static function getIcon(): string
    {
        return 'file-alt';
    }

    public function supportsContext(string $context): bool
    {
        return AdministrationContext::getName() === $context;
    }

    public function isRequired(string $context, ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }
}
