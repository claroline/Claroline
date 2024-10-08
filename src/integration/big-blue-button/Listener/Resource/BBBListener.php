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

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\BigBlueButtonBundle\Manager\BBBManager;
use Claroline\BigBlueButtonBundle\Manager\EvaluationManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BBBListener extends ResourceComponent implements EvaluatedResourceInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly PlatformConfigurationHandler $config,
        private readonly SerializerProvider $serializer,
        private readonly BBBManager $bbbManager,
        private readonly EvaluationManager $evaluationManager
    ) {
    }

    public static function getName(): string
    {
        return 'claroline_big_blue_button';
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

        $userEvaluation = null;
        if ($this->tokenStorage->getToken()?->getUser() instanceof User) {
            $attempt = $this->evaluationManager->update($resource->getResourceNode(), $this->tokenStorage->getToken()?->getUser());
            $userEvaluation = $attempt->getResourceUserEvaluation();
        }

        return [
            'resource' => $this->serializer->serialize($resource),
            'userEvaluation' => $this->serializer->serialize($userEvaluation, [SerializerInterface::SERIALIZE_MINIMAL]),
            'servers' => $this->bbbManager->getServers(),
            'allowRecords' => $allowRecords,
            'canStart' => $canStart,
            'joinStatus' => $joinStatus,
            'lastRecording' => $lastRecording,
        ];
    }

    /** @param BBB $resource */
    public function update(AbstractResource $resource, array $data): ?array
    {
        return [
            'resource' => $this->serializer->serialize($resource),
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
