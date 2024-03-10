<?php

namespace Claroline\TagBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\DesktopContext;

class TagsTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'tags';
    }

    public static function getIcon(): string
    {
        return 'tags';
    }

    public function supportsContext(string $context): bool
    {
        return DesktopContext::getName() === $context;
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
