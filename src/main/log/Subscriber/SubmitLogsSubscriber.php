<?php

namespace Claroline\LogBundle\Subscriber;

use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Manager\LogManager;
use Claroline\LogBundle\Messenger\Message\CreateFunctionalLog;
use Claroline\LogBundle\Messenger\Message\CreateMessageLog;
use Claroline\LogBundle\Messenger\Message\CreateOperationalLog;
use Claroline\LogBundle\Messenger\Message\CreateSecurityLog;
use Claroline\LogBundle\Messenger\Message\SubmitLogs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Sends all the logs created during the process of the current request to the messenger to be persisted in DB.
 */
class SubmitLogsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageBusInterface $messageBus,
        private readonly LogManager $logManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => ['submitProcessLogs', -50],
        ];
    }

    /**
     * Sends all the collected logs to the messenger.
     */
    public function submitProcessLogs(TerminateEvent $event): void
    {
        $doerIp = $this->getDoerIp($event->getRequest());

        $this->submitLogs(CreateFunctionalLog::class, $doerIp, $this->logManager->getFunctionalLogs());
        $this->logManager->resetFunctionalLogs();

        $this->submitLogs(CreateOperationalLog::class, $doerIp, $this->logManager->getOperationalLogs());
        $this->logManager->resetOperationalLogs();

        $this->submitLogs(CreateSecurityLog::class, $doerIp, $this->logManager->getSecurityLogs());
        $this->logManager->resetSecurityLogs();

        $this->submitLogs(CreateMessageLog::class, $doerIp, $this->logManager->getMessageLogs());
        $this->logManager->resetMessageLogs();
    }

    private function submitLogs(string $type, string $doerIp, array $logs): void
    {
        if (empty($logs)) {
            return;
        }

        $stamps = [];
        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()?->getUser() instanceof User) {
            $stamps[] = new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId());
        }

        // dispatch stashed messages
        $this->messageBus->dispatch(new SubmitLogs($type, $doerIp, $logs), $stamps);
    }

    private function getDoerIp(Request $request = null): string
    {
        $doerIp = 'CLI';
        if ($request) {
            $doerIp = $request->getClientIp();
        }

        return $doerIp;
    }
}
