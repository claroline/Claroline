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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectPlatform;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectResource;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;

class LogConnectManager
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function manageConnection(Log $log): void
    {
        $action = $log->getAction();
        $dateLog = $log->getDateLog();
        $user = $log->getDoer();

        if (!is_null($user)) {
            switch ($action) {
                case SecurityEvents::USER_LOGIN:
                    $this->createLogConnectPlatform($user, $dateLog);

                    break;

                case LogWorkspaceEnterEvent::ACTION:
                    $logWorkspace = $log->getWorkspace();

                    // Creates workspace log for current connection
                    $this->createLogConnectWorkspace($user, $logWorkspace, $dateLog);

                    break;

                case LogResourceReadEvent::ACTION:
                    $logResourceNode = $log->getResourceNode();
                    $details = $log->getDetails();
                    $embedded = $details && isset($details['embedded']) ? $details['embedded'] : false;

                    // Creates resource log for current connection
                    $this->createLogConnectResource($user, $logResourceNode, $dateLog, $embedded);

                    break;
            }
        }
    }

    private function createLogConnectPlatform(User $user, \DateTime $date): void
    {
        // Creates a new platform connection with no duration for the current connection
        $newConnection = new LogConnectPlatform();
        $newConnection->setUser($user);
        $newConnection->setConnectionDate($date);

        $this->om->persist($newConnection);
        $this->om->flush();
    }

    private function createLogConnectWorkspace(User $user, Workspace $workspace, \DateTime $date): void
    {
        // Creates a new workspace connection with no duration for the current connection
        $newConnection = new LogConnectWorkspace();
        $newConnection->setUser($user);
        $newConnection->setConnectionDate($date);
        $newConnection->setWorkspace($workspace);

        $this->om->persist($newConnection);
        $this->om->flush();
    }

    private function createLogConnectResource(User $user, ResourceNode $node, \DateTime $date, $embedded = false): void
    {
        // Creates a new resource connection with no duration for the current connection
        $newConnection = new LogConnectResource();
        $newConnection->setUser($user);
        $newConnection->setConnectionDate($date);
        $newConnection->setResource($node);
        $newConnection->setEmbedded($embedded);

        $this->om->persist($newConnection);
        $this->om->flush();
    }
}
