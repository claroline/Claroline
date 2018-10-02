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
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectPlatform;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
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
    }

    public function manageConnection(Log $log)
    {
        $action = $log->getAction();
        $dateLog = $log->getDateLog();
        $user = $log->getDoer();

        switch ($action) {
            case LogUserLoginEvent::ACTION:
                $this->managePlatformConnection($user, $dateLog);
                break;
        }
    }

    private function managePlatformConnection(User $user, \DateTime $date)
    {
        // Fetches connections with no duration
        $openConnections = $this->logPlatformRepo->findBy(
            ['user' => $user, 'duration' => null],
            ['connectionDate' => 'DESC']
        );
        $this->om->startFlushSuite();

        // Computes duration for the most recent connection (with no duration) based on last log for user
        if (0 < count($openConnections)) {
            $connection = $openConnections[0];
            // Fetches all user's logs by desc order
            $userLogs = $this->logRepo->findBy(['doer' => $user], ['dateLog' => 'DESC']);

            // Retrieves the first log which date is lower than log triggering this function
            $index = 0;

            while (isset($userLogs[$index]) && $userLogs[$index]->getDateLog() >= $date) {
                ++$index;
            }
            if (isset($userLogs[$index])) {
                $logDate = $userLogs[$index]->getDateLog();
                $connectionDate = $connection->getConnectionDate();

                if ($logDate >= $connectionDate) {
                    $duration = $logDate->getTimestamp() - $connectionDate->getTimestamp();
                    $connection->setDuration($duration);
                    $this->om->persist($connection);
                }
            }
        }

        // Creates a new platform connection with no duration for this current connection
        $newConnection = new LogConnectPlatform();
        $newConnection->setUser($user);
        $newConnection->setConnectionDate($date);
        $this->om->persist($newConnection);
        $this->om->endFlushSuite();
    }
}
