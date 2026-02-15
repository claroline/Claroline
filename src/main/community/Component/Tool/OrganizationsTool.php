<?php

namespace Claroline\CommunityBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\ToolComponent;
use Claroline\CoreBundle\Component\Context\AdministrationContext;

class OrganizationsTool extends ToolComponent
{
    public static function getName(): string
    {
        return 'organizations';
    }

    public static function getIcon(): string
    {
        return 'building';
    }

    public function supportsContext(string $context): bool
    {
        return AdministrationContext::getName() === $context;
    }

    public function isRequired(string $context, ?ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }
}
