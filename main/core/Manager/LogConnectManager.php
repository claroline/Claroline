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
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectResource;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectTool;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
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
    private $orderedToolRepo;

    private $logPlatformRepo;
    private $logWorkspaceRepo;
    private $logToolRepo;
    private $logResourceRepo;

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
        $this->orderedToolRepo = $om->getRepository('ClarolineCoreBundle:Tool\OrderedTool');

        $this->logPlatformRepo = $om->getRepository('ClarolineCoreBundle:Log\Connection\LogConnectPlatform');
        $this->logWorkspaceRepo = $om->getRepository('ClarolineCoreBundle:Log\Connection\LogConnectWorkspace');
        $this->logToolRepo = $om->getRepository('ClarolineCoreBundle:Log\Connection\LogConnectTool');
        $this->logResourceRepo = $om->getRepository('ClarolineCoreBundle:Log\Connection\LogConnectResource');
    }

    public function manageConnection(Log $log)
    {
        $action = $log->getAction();
        $dateLog = $log->getDateLog();
        $user = $log->getDoer();

        switch ($action) {
            case LogUserLoginEvent::ACTION:
                $this->om->startFlushSuite();

                $this->createLogConnectPlatform($user, $dateLog);

                $this->om->endFlushSuite();
                break;
            case LogWorkspaceEnterEvent::ACTION:
                $this->om->startFlushSuite();

                $logWorkspace = $log->getWorkspace();

                // Computes duration for the most recent workspace connection (with no duration)
                // for the current user's session
                $workspaceConnection = $this->getComputableWorkspace($user);

                if (!is_null($workspaceConnection)) {
                    // Ignores log if previous workspace entering log and this one are associated to the same workspace
                    // for the current session
                    if ($workspaceConnection->getWorkspace() === $logWorkspace) {
                        break;
                    } else {
                        $this->computeConnectionDuration($workspaceConnection, $dateLog);
                    }
                }
                // Creates workspace log for current connection
                $this->createLogConnectWorkspace($user, $logWorkspace, $dateLog);

                $this->om->endFlushSuite();
                break;
            /*
             * When opening workspace tool, computes duration for :
             * - last resource
             * - last workspace tool
             */
            case LogWorkspaceToolReadEvent::ACTION:
                $this->om->startFlushSuite();

                $logWorkspace = $log->getWorkspace();
                $logToolName = $log->getToolName();

                // Computes duration for the most recent workspace tool connection (with no duration)
                // for the current user's session
                $toolConnection = $this->getComputableLogTool($user);
                $resourceConnection = $this->getComputableLogResource($user);

                // Computes last resource duration
                if (!is_null($toolConnection)) {
                    $this->computeConnectionDuration($resourceConnection, $dateLog);
                }
                // Computes last workspace tool duration
                if (!is_null($toolConnection)) {
                    // Ignores log if previous workspace tool opening log and this one are associated to the same workspace tool
                    // for the current session
                    if ($toolConnection->getWorkspace() === $logWorkspace && $toolConnection->getToolName() === $logToolName) {
                        break;
                    } else {
                        $this->computeConnectionDuration($toolConnection, $dateLog);
                    }
                }
                // Creates workspace tool log for current connection
                $this->createLogConnectTool($user, $logToolName, $dateLog, $logWorkspace);

                $this->om->endFlushSuite();
                break;
            /*
             * When opening resource, computes duration for :
             * - last workspace tool
             * - last resource
             */
            case LogResourceReadEvent::ACTION:
                $this->om->startFlushSuite();

                $logResourceNode = $log->getResourceNode();

                // Computes duration for the most recent resource opening (with no duration)
                // for the current user's session
                $resourceConnection = $this->getComputableLogResource($user);
                $toolConnection = $this->getComputableLogTool($user);

                // Computes last workspace tool duration
                if (!is_null($toolConnection)) {
                    $this->computeConnectionDuration($toolConnection, $dateLog);
                }
                // Computes last resource duration
                if (!is_null($resourceConnection)) {
                    // Ignores log if previous resource opening log and this one are associated to the same resource
                    // for the current session
                    if ($resourceConnection->getResource() === $logResourceNode) {
                        break;
                    } else {
                        $this->computeConnectionDuration($resourceConnection, $dateLog);
                    }
                }
                // Creates resource log for current connection
                $this->createLogConnectResource($user, $logResourceNode, $dateLog);

                $this->om->endFlushSuite();
                break;
        }
    }

    private function getLogConnectPlatform(User $user)
    {
        // Fetches connections with no duration
        $openConnections = $this->logPlatformRepo->findBy(
            ['user' => $user, 'duration' => null],
            ['connectionDate' => 'DESC']
        );

        return 0 < count($openConnections) ? $openConnections[0] : null;
    }

    private function getLogConnectWorkspace(User $user)
    {
        // Fetches connections with no duration
        $openConnections = $this->logWorkspaceRepo->findBy(
            ['user' => $user, 'duration' => null],
            ['connectionDate' => 'DESC']
        );

        return 0 < count($openConnections) ? $openConnections[0] : null;
    }

    private function getLogConnectTool(User $user)
    {
        // Fetches connections with no duration
        $openConnections = $this->logToolRepo->findBy(
            ['user' => $user, 'duration' => null],
            ['connectionDate' => 'DESC']
        );

        return 0 < count($openConnections) ? $openConnections[0] : null;
    }

    private function getLogConnectResource(User $user)
    {
        // Fetches connections with no duration
        $openConnections = $this->logResourceRepo->findBy(
            ['user' => $user, 'duration' => null],
            ['connectionDate' => 'DESC']
        );

        return 0 < count($openConnections) ? $openConnections[0] : null;
    }

    private function getComputableWorkspace(User $user)
    {
        // Gets the most recent workspace connection (with no duration) for the current user's session
        $workspaceConnection = $this->getLogConnectWorkspace($user);
        // Gets current user's connection to platform
        $platformConnection = $this->getLogConnectPlatform($user);

        $isComputable = !is_null($workspaceConnection) && $this->isComputableWithoutLogs($workspaceConnection, $platformConnection);

        return $isComputable ? $workspaceConnection : null;
    }

    private function getComputableLogTool(User $user)
    {
        // Gets the most recent workspace tool connection (with no duration) for the current user's session
        $toolConnection = $this->getLogConnectTool($user);
        // Gets current user's connection to platform
        $platformConnection = $this->getLogConnectPlatform($user);

        $isComputable = !is_null($toolConnection) && $this->isComputableWithoutLogs($toolConnection, $platformConnection);

        return $isComputable ? $toolConnection : null;
    }

    private function getComputableLogResource(User $user)
    {
        // Gets the most recent resource opening (with no duration) for the current user's session
        $resourceConnection = $this->getLogConnectResource($user);
        // Gets current user's connection to platform
        $platformConnection = $this->getLogConnectPlatform($user);

        $isComputable = !is_null($resourceConnection) && $this->isComputableWithoutLogs($resourceConnection, $platformConnection);

        return $isComputable ? $resourceConnection : null;
    }

    private function computeConnectionDuration(AbstractLogConnect $connection, \DateTime $date)
    {
        $connectionDate = $connection->getConnectionDate();

        if ($date >= $connectionDate) {
            $duration = $date->getTimestamp() - $connectionDate->getTimestamp();
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

    private function createLogConnectTool(User $user, $toolName, \DateTime $date, Workspace $workspace = null)
    {
        $orderedTool = $this->orderedToolRepo->findOneBy(['workspace' => $workspace, 'name' => $toolName]);

        if (!is_null($orderedTool)) {
            // Creates a new workspace tool connection with no duration for the current connection
            $newConnection = new LogConnectTool();
            $newConnection->setUser($user);
            $newConnection->setConnectionDate($date);
            $newConnection->setTool($orderedTool);
            $this->om->persist($newConnection);
            $this->om->flush();
        }
    }

    private function createLogConnectResource(User $user, ResourceNode $node, \DateTime $date)
    {
        // Creates a new resource connection with no duration for the current connection
        $newConnection = new LogConnectResource();
        $newConnection->setUser($user);
        $newConnection->setConnectionDate($date);
        $newConnection->setResource($node);
        $this->om->persist($newConnection);
        $this->om->flush();
    }

    private function isComputableWithoutLogs(AbstractLogConnect $connection, LogConnectPlatform $platformConnect)
    {
        return $connection->getConnectionDate() > $platformConnect->getConnectionDate();
    }
}
