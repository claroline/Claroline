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

    /**
     * BBBListener constructor.
     *
     * @param PlatformConfigurationHandler $config
     * @param BBBManager                   $bbbManager
     * @param SerializerProvider           $serializer
     */
    public function __construct(
        PlatformConfigurationHandler $config,
        BBBManager $bbbManager,
        SerializerProvider $serializer
    ) {
        $this->config = $config;
        $this->bbbManager = $bbbManager;
        $this->serializer = $serializer;
    }

    /**
     * Loads the BBB resource.
     *
     * @param LoadResourceEvent $event
     */
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
            $lastRecording = $this->bbbManager->getLastRecording($bbb);
        }

        $event->setData([
            'bbb' => $this->serializer->serialize($bbb),
            'allowRecords' => $allowRecords,
            'canStart' => $canStart,
            'joinStatus' => $joinStatus,
            'lastRecording' => $lastRecording,
        ]);
        $event->stopPropagation();
    }

    /**
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        /** @var BBB $bbb */
        $bbb = $event->getResource();
        $this->bbbManager->deleteMeetingRecordings($bbb);

        $event->stopPropagation();
    }
}
