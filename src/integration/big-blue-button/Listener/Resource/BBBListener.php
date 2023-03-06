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
use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\BigBlueButtonBundle\Manager\BBBManager;
use Claroline\BigBlueButtonBundle\Manager\EvaluationManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BBBListener
{
    private TokenStorageInterface $tokenStorage;
    private PlatformConfigurationHandler $config;
    private SerializerProvider $serializer;
    private BBBManager $bbbManager;
    private EvaluationManager $evaluationManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $config,
        SerializerProvider $serializer,
        BBBManager $bbbManager,
        EvaluationManager $evaluationManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
        $this->serializer = $serializer;
        $this->bbbManager = $bbbManager;
        $this->evaluationManager = $evaluationManager;
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

        $userEvaluation = null;
        if ($this->tokenStorage->getToken()->getUser() instanceof User) {
            $attempt = $this->evaluationManager->update($bbb->getResourceNode(), $this->tokenStorage->getToken()->getUser());
            $userEvaluation = $attempt->getResourceUserEvaluation();
        }

        $event->setData([
            'userEvaluation' => $this->serializer->serialize($userEvaluation, [SerializerInterface::SERIALIZE_MINIMAL]),
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
