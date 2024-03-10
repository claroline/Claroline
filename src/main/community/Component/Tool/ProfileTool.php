<?php

namespace Claroline\CommunityBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\AccountContext;

class ProfileTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'profile';
    }

    public static function getIcon(): string
    {
        return 'user-circle';
    }

    public function isRequired(string $context, ?string $contextId): bool
    {
        return true;
    }

    public function supportsContext(string $context): bool
    {
        return AccountContext::getName() === $context;
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        return [];
    }

    public function configure(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        return [];
    }
}
