<?php

namespace Claroline\LogBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\LogBundle\Messenger\Message\CreateFunctionalLog;
use Claroline\LogBundle\Messenger\Message\CreateMessageLog;
use Claroline\LogBundle\Messenger\Message\CreateOperationalLog;
use Claroline\LogBundle\Messenger\Message\CreateSecurityLog;

/**
 * Collects Logs generated during the process
 * and sends them (at the end of the process) to the messenger for creation.
 */
class LogManager
{
    /**
     * The list of functional logs created during the current process.
     */
    private array $functionalLogs = [];

    /**
     * The list of operational logs created during the current process.
     */
    private array $operationalLogs = [];

    /**
     * The list of security logs created during the current process.
     */
    private array $securityLogs = [];

    /**
     * The list of message logs created during the current process.
     */
    private array $messageLogs = [];

    /**
     * Create a new functional log.
     */
    public function logFunctional(string $action, string $message, User $doer = null, Workspace $workspace = null, ResourceNode $resourceNode = null): void
    {
        $this->functionalLogs[] = new CreateFunctionalLog(
            new \DateTime(),
            $action,
            $message,
            $doer?->getId(),
            $workspace?->getId(),
            $resourceNode?->getId()
        );
    }

    public function getFunctionalLogs(): array
    {
        return $this->functionalLogs;
    }

    public function resetFunctionalLogs(): void
    {
        $this->functionalLogs = [];
    }

    /**
     * Create a new operational log.
     */
    public function logOperational(
        string $action,
        string $message,
        User $doer = null,
        string $objectClass,
        string $objectId,
        string $contextName,
        ?string $contextId = null,
        ?array $changeset = []
    ): void {
        $this->operationalLogs[] = new CreateOperationalLog(
            new \DateTime(),
            $action,
            $message,
            $doer?->getId(),
            $objectClass,
            $objectId,
            $contextName,
            $contextId,
            $changeset
        );
    }

    public function getOperationalLogs(): array
    {
        return $this->operationalLogs;
    }

    public function resetOperationalLogs(): void
    {
        $this->operationalLogs = [];
    }

    /**
     * Create a new security log.
     */
    public function logSecurity(string $action, string $message, User $doer = null, User $target = null): void
    {
        $this->securityLogs[] = new CreateSecurityLog(
            new \DateTime(),
            $action,
            $message,
            $doer?->getId(),
            $target?->getId()
        );
    }

    public function getSecurityLogs(): array
    {
        return $this->securityLogs;
    }

    public function resetSecurityLogs(): void
    {
        $this->securityLogs = [];
    }

    /**
     * Create a new message log.
     */
    public function logMessage(string $action, string $message, User $doer = null, User $receiver = null): void
    {
        $this->messageLogs[] = new CreateMessageLog(
            new \DateTime(),
            $action,
            $message,
            $doer?->getId(),
            $receiver?->getId()
        );
    }

    public function getMessageLogs(): array
    {
        return $this->messageLogs;
    }

    public function resetMessageLogs(): void
    {
        $this->messageLogs = [];
    }
}
