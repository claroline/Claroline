<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\BigBlueButtonBundle\Manager\BBBManager;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class BBBListener
{
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var BBBManager */
    private $bbbManager;
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(
        PlatformConfigurationHandler $config,
        BBBManager $bbbManager,
        SerializerProvider $serializer
    ) {
        $this->config = $config;
        $this->bbbManager = $bbbManager;
        $this->serializer = $serializer;
    }

    public function onLoad(LoadResourceEvent $event)
    {
        /** @var BBB $bbb */
        $bbb = $event->getResource();

        $joinStatus = 'closed';
        $canStart = $this->bbbManager->canStartMeeting($bbb);
        if ($canStart) {
            $joinStatus = $this->bbbManager->canJoinMeeting($bbb);
        }

        $allowRecords = $this->config->getParameter('bbb.allow_records');

        $lastRecording = null;
        if ($allowRecords && $bbb->isRecord()) {
            // not the best place to do it
            $this->bbbManager->syncRecordings($bbb);

            if ($bbb->getLastRecording()) {
                $lastRecording = $this->serializer->serialize($bbb->getLastRecording());
            }
        }

        $event->setData([
            'servers' => $this->bbbManager->getServers(),
            'bbb' => $this->serializer->serialize($bbb),
            'allowRecords' => $allowRecords,
            'canStart' => $canStart,
            'joinStatus' => $joinStatus,
            'lastRecording' => $lastRecording,
        ]);
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        /** @var BBB $bbb */
        $bbb = $event->getResource();
        if (!$event->isSoftDelete()) {
            $this->bbbManager->deleteRecordings($bbb);
        }

        $event->stopPropagation();
    }
}
