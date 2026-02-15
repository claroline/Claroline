<?php

namespace Claroline\LogBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\ToolComponent;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\LogBundle\Component\Log\LogProvider;

class LogsTool extends ToolComponent
{
    public function __construct(
        private readonly LogProvider $logProvider
    ) {
    }

    public static function getName(): string
    {
        return 'logs';
    }

    public static function getIcon(): string
    {
        return 'shoe-prints';
    }

    public function supportsContext(string $context): bool
    {
        return AdministrationContext::getName() === $context;
    }

    public function isRequired(string $context, ?ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }

    public function open(OrderedTool $tool, string $context, ?ContextSubjectInterface $contextSubject = null): ?array
    {
        return [
            'types' => [
                'functional' => $this->logProvider->getFunctionalLogs(),
                'operational' => $this->logProvider->getOperationalLogs(),
                'security' => $this->logProvider->getSecurityLogs(),
                'message' => $this->logProvider->getMessageLogs(),
            ],
        ];
    }
}
