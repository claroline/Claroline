<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Log\Connection\AbstractLogConnect;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectPlatform;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.log_connect")
 */
class LogConnectManager
{
    /** @var FinderProvider */
    private $finder;

    /**
     * @var ObjectManager
     */
    private $om;

    private $logRepo;
    private $logPlatformRepo;
    private $logWorkspaceRepo;

    /**
     * @DI\InjectParams({
     *     "finder" = @DI\Inject("claroline.api.finder"),
     *     "om"     = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param FinderProvider $finder
     * @param ObjectManager  $om
     */
    public function __construct(FinderProvider $finder, ObjectManager $om)
    {
        $this->finder = $finder;
        $this->om = $om;

        $this->logRepo = $om->getRepository('ClarolineCoreBundle:Log\Log');
        $this->logPlatformRepo = $om->getRepository('ClarolineCoreBundle:Log\Connection\LogConnectPlatform');
        $this->logWorkspaceRepo = $om->getRepository('ClarolineCoreBundle:Log\Connection\LogConnectWorkspace');
    }

    public function manageConnection(Log $log)
    {
        $action = $log->getAction();
        $dateLog = $log->getDateLog();
        $user = $log->getDoer();

        switch ($action) {
            case LogUserLoginEvent::ACTION:
                $this->om->startFlushSuite();

                // Computes duration for the most recent connection (with no duration) based on last log for user
                /*
                $platformConnection = $this->getLogConnectPlatformToCompute($user);
                $workspaceConnection = $this->getLogConnectWorkspaceToCompute($user);
                $previousLog = (!is_null($platformConnection) || !is_null($workspaceConnection)) ?
                    $this->getPreviousUserLog($user, $dateLog) :
                    null;

                if (!is_null($previousLog)) {
                    if (!is_null($platformConnection)) {
                        $this->computeLastConnectionDuration($platformConnection, $previousLog);
                    }
                    if (!is_null($workspaceConnection)) {
                        $this->computeLastConnectionDuration($workspaceConnection, $previousLog);
                    }
                }
                */
                $this->createLogConnectPlatform($user, $dateLog);

                $this->om->endFlushSuite();
                break;
            case LogWorkspaceEnterEvent::ACTION:
                $this->om->startFlushSuite();

                $logWorkspace = $log->getWorkspace();

                /*
                // Computes duration for the most recent connection (with no duration) based on last log for user
                $workspaceConnection = $this->getLogConnectWorkspaceToCompute($user);

                // Ignores log if previous workspace entering log & this one are associated to the same workspace
                if (!is_null($workspaceConnection) && $workspaceConnection->getWorkspace() === $logWorkspace) {
                    break;
                }

                $previousLog = !is_null($workspaceConnection) ?
                    $this->getPreviousUserLog($user, $dateLog) :
                    null;

                if (!is_null($previousLog)) {
                    $this->computeLastConnectionDuration($workspaceConnection, $previousLog);
                }
                */
                $this->createLogConnectWorkspace($user, $logWorkspace, $dateLog);

                $this->om->endFlushSuite();
                break;
        }
    }

    private function getPreviousUserLog(User $user, \DateTime $date)
    {
        $userLogs = $this->logRepo->findBy(['doer' => $user], ['dateLog' => 'DESC']);
        $index = 0;

        // Retrieves the first log which date is lower than log triggering this function
        while (isset($userLogs[$index]) && $userLogs[$index]->getDateLog() >= $date) {
            ++$index;
        }

        return isset($userLogs[$index]) ? $userLogs[$index] : null;
    }

    private function getLogConnectPlatformToCompute(User $user)
    {
        // Fetches connections with no duration
        $openConnections = $this->logPlatformRepo->findBy(
            ['user' => $user, 'duration' => null],
            ['connectionDate' => 'DESC']
        );

        return 0 < count($openConnections) ? $openConnections[0] : null;
    }

    private function getLogConnectWorkspaceToCompute(User $user)
    {
        // Fetches connections with no duration
        $openConnections = $this->logWorkspaceRepo->findBy(
            ['user' => $user, 'duration' => null],
            ['connectionDate' => 'DESC']
        );

        return 0 < count($openConnections) ? $openConnections[0] : null;
    }

    private function computeLastConnectionDuration(AbstractLogConnect $connection, Log $previousLog)
    {
        $logDate = $previousLog->getDateLog();
        $connectionDate = $connection->getConnectionDate();

        if ($logDate >= $connectionDate) {
            $duration = $logDate->getTimestamp() - $connectionDate->getTimestamp();
            $connection->setDuration($duration);
            $this->om->persist($connection);
            $this->om->flush();
        }
    }

    private function createLogConnectPlatform(User $user, \DateTime $date)
    {
        // Creates a new platform connection with no duration for the current connection
        $newConnection = new LogConnectPlatform();
        $newConnection->setUser($user);
        $newConnection->setConnectionDate($date);
        $this->om->persist($newConnection);
        $this->om->flush();
    }

    private function createLogConnectWorkspace(User $user, Workspace $workspace, \DateTime $date)
    {
        // Creates a new workspace connection with no duration for the current connection
        $newConnection = new LogConnectWorkspace();
        $newConnection->setUser($user);
        $newConnection->setConnectionDate($date);
        $newConnection->setWorkspace($workspace);
        $this->om->persist($newConnection);
        $this->om->flush();
    }
}
