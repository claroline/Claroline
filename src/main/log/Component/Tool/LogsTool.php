<?php

namespace Claroline\LogBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\AccountContext;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\LogBundle\Component\Log\LogProvider;

class LogsTool extends AbstractTool
{
    public function __construct(
        private readonly LogProvider $logProvider
    ) {
    }

    public static function getName(): string
    {
        return 'logs';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            AccountContext::getName(),
            AdministrationContext::getName(),
        ]);
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        if (AdministrationContext::getName() === $context) {
            return [
                'types' => [
                    'functional' => $this->logProvider->getFunctionalLogs(),
                    'operational' => $this->logProvider->getOperationalLogs(),
                    'security' => $this->logProvider->getSecurityLogs(),
                    'message' => $this->logProvider->getMessageLogs(),
                ],
            ];
        }

        return [];
    }

    public function configure(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        return [];
    }
}
