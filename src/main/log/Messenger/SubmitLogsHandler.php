<?php

namespace Claroline\LogBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\GeoIp\GeoIpInfoProviderInterface;
use Claroline\LogBundle\Entity\AbstractLog;
use Claroline\LogBundle\Entity\FunctionalLog;
use Claroline\LogBundle\Entity\MessageLog;
use Claroline\LogBundle\Entity\OperationalLog;
use Claroline\LogBundle\Entity\SecurityLog;
use Claroline\LogBundle\Messenger\Message\AbstractCreateLog;
use Claroline\LogBundle\Messenger\Message\CreateFunctionalLog;
use Claroline\LogBundle\Messenger\Message\CreateMessageLog;
use Claroline\LogBundle\Messenger\Message\CreateOperationalLog;
use Claroline\LogBundle\Messenger\Message\CreateSecurityLog;
use Claroline\LogBundle\Messenger\Message\SubmitLogs;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SubmitLogsHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly ?GeoIpInfoProviderInterface $geoIpInfoProvider = null
    ) {
    }

    public function __invoke(SubmitLogs $submitLogs): void
    {
        $logsToCreate = $submitLogs->getLogs();
        foreach ($logsToCreate as $createLog) {
            switch ($submitLogs->getType()) {
                case CreateFunctionalLog::class:
                    $logEntry = $this->createFunctionLog($createLog);
                    break;

                case CreateOperationalLog::class:
                    $logEntry = $this->createOperationalLog($createLog);
                    break;

                case CreateSecurityLog::class:
                    $logEntry = $this->createSecurityLog($createLog);
                    break;

                case CreateMessageLog::class:
                    $logEntry = $this->createMessageLog($createLog);
                    break;

                default:
                    continue 2;
            }

            $this->populateLog($logEntry, $createLog, $submitLogs->getDoerIp());
            $this->om->persist($logEntry);
        }

        $this->om->flush();
    }

    private function createFunctionLog(CreateFunctionalLog $createLog): FunctionalLog
    {
        $logEntry = new FunctionalLog();

        if ($createLog->getWorkspaceId()) {
            $workspace = $this->om->getRepository(Workspace::class)->find($createLog->getWorkspaceId());
            $logEntry->setWorkspace($workspace);
        }

        if ($createLog->getResourceNodeId()) {
            $resourceNode = $this->om->getRepository(ResourceNode::class)->find($createLog->getResourceNodeId());
            $logEntry->setResource($resourceNode);
        }

        return $logEntry;
    }

    private function createOperationalLog(CreateOperationalLog $createLog): OperationalLog
    {
        $logEntry = new OperationalLog();

        $logEntry->setContextName($createLog->getContextName());
        $logEntry->setContextId($createLog->getContextId());
        $logEntry->setObjectClass($createLog->getObjectClass());
        $logEntry->setObjectId($createLog->getObjectId());
        $logEntry->setChangeset($createLog->getChangeset());

        return $logEntry;
    }

    private function createSecurityLog(CreateSecurityLog $createLog): SecurityLog
    {
        $logEntry = new SecurityLog();

        if ($createLog->getTargetId()) {
            $target = $this->om->getRepository(User::class)->find($createLog->getTargetId());
            $logEntry->setTarget($target);
        }

        return $logEntry;
    }

    private function createMessageLog(CreateMessageLog $createLog): MessageLog
    {
        $logEntry = new MessageLog();

        if ($createLog->getReceiverId()) {
            $receiver = $this->om->getRepository(User::class)->find($createLog->getReceiverId());
            $logEntry->setReceiver($receiver);
        }

        return $logEntry;
    }

    private function populateLog(AbstractLog $logEntry, AbstractCreateLog $createLog, string $doerIp): void
    {
        $logEntry->setDate($createLog->getDate());
        $logEntry->setEvent($createLog->getAction());
        $logEntry->setDetails($createLog->getDetails());

        if ($createLog->getDoerId()) {
            $doer = $this->om->getRepository(User::class)->find($createLog->getDoerId());
            $logEntry->setDoer($doer);
        }

        $logEntry->setDoerIp($doerIp);

        $doerLocation = $this->getDoerLocation($doerIp);
        $logEntry->setDoerCountry($doerLocation['country']);
        $logEntry->setDoerCity($doerLocation['city']);
    }

    private function getDoerLocation(string $doerIp): array
    {
        $doerCountry = null;
        $doerCity = null;
        if ($this->geoIpInfoProvider && 'CLI' !== $doerIp) {
            $geoIpInfo = $this->geoIpInfoProvider->getGeoIpInfo($doerIp);

            if ($geoIpInfo) {
                $doerCountry = $geoIpInfo->getCountry();
                $doerCity = $geoIpInfo->getCity();
            }
        }

        return [
            'city' => $doerCity,
            'country' => $doerCountry,
        ];
    }
}
