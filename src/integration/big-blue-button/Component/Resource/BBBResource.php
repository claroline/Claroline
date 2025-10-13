<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Component\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\BigBlueButtonBundle\Manager\BBBManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;

final class BBBResource extends ResourceComponent implements EvaluatedResourceInterface
{
    public function __construct(
        private readonly PlatformConfigurationHandler $config,
        private readonly SerializerProvider $serializer,
        private readonly BBBManager $bbbManager
    ) {
    }

    public static function getName(): string
    {
        return 'claroline_big_blue_button';
    }

    public static function supportsScore(): bool
    {
        return false;
    }

    public static function supportsAttempts(): bool
    {
        return false;
    }

    /** @param BBB $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $joinStatus = 'closed';
        $canStart = $this->bbbManager->canStartMeeting($resource);
        if ($canStart) {
            $joinStatus = $this->bbbManager->canJoinMeeting($resource);
        }

        $allowRecords = $this->config->getParameter('bbb.allow_records');

        $lastRecording = null;
        if ($allowRecords && $resource->isRecord()) {
            // not the best place to do it
            $this->bbbManager->syncRecordings($resource);

            if ($resource->getLastRecording()) {
                $lastRecording = $this->serializer->serialize($resource->getLastRecording());
            }
        }

        return [
            'servers' => $this->bbbManager->getServers(),
            'allowRecords' => $allowRecords,
            'canStart' => $canStart,
            'joinStatus' => $joinStatus,
            'lastRecording' => $lastRecording,
        ];
    }

    /** @param BBB $resource */
    public function delete(AbstractResource $resource, FileBag $fileBag, bool $softDelete = true): bool
    {
        if (!$softDelete) {
            $this->bbbManager->deleteRecordings($resource);
        }

        // close the room
        $this->bbbManager->endMeeting($resource);

        return true;
    }
}
