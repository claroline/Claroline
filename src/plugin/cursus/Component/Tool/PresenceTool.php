<?php

namespace Claroline\CursusBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\PublicContext;

class PresenceTool extends AbstractTool
{
    public static function getName(): string
    {
        return 'presence';
    }

    public function supportsContext(string $context): bool
    {
        return PublicContext::getName() === $context;
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
