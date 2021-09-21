<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Listener\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Claroline\TeamBundle\Manager\TeamManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DropzoneListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var DropzoneManager */
    private $dropzoneManager;

    /** @var SerializerProvider */
    private $serializer;

    /** @var TeamManager */
    private $teamManager;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        DropzoneManager $dropzoneManager,
        SerializerProvider $serializer,
        TeamManager $teamManager,
        TranslatorInterface $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->dropzoneManager = $dropzoneManager;
        $this->serializer = $serializer;
        $this->teamManager = $teamManager;
        $this->translator = $translator;
    }

    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Dropzone $dropzone */
        $dropzone = $event->getResource();

        $event->setData(
            $this->getDropzoneData($dropzone)
        );
        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        /** @var Dropzone $dropzone */
        $dropzone = $event->getResource();

        $copy = $this->dropzoneManager->copyDropzone($dropzone, $event->getCopy());

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        /** @var Dropzone $dropzone */
        $dropzone = $event->getResource();

        $this->dropzoneManager->delete($dropzone);

        $event->stopPropagation();
    }

    // todo : move me elsewhere (in a manager for ex)
    private function getDropzoneData(Dropzone $dropzone)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof User) {
            $user = null;
        }

        $resourceNode = $dropzone->getResourceNode();

        $serializedTeams = [];
        $teams = !empty($user) ?
            $this->teamManager->getTeamsByUserAndWorkspace($user, $resourceNode->getWorkspace()) :
            [];

        foreach ($teams as $team) {
            $serializedTeams[] = $this->serializer->serialize($team);
        }
        $myDrop = null;
        $finishedPeerDrops = [];
        $errorMessage = null;
        $teamId = null;

        if (!$dropzone->getDropClosed() && $dropzone->getAutoCloseDropsAtDropEndDate() && !$dropzone->getManualPlanning()) {
            $dropEndDate = $dropzone->getDropEndDate();

            if ($dropEndDate < new \DateTime()) {
                $this->dropzoneManager->closeAllUnfinishedDrops($dropzone);
            }
        }

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                $myDrop = !empty($user) ? $this->dropzoneManager->getUserDrop($dropzone, $user) : null;
                $finishedPeerDrops = $this->dropzoneManager->getFinishedPeerDrops($dropzone, $user);
                break;
            case Dropzone::DROP_TYPE_TEAM:
                $drops = [];
                $teamsIds = array_map(function ($team) {
                    return $team['id'];
                }, $serializedTeams);

                /* Fetches team drops associated to user */
                $teamDrops = !empty($user) ? $this->dropzoneManager->getTeamDrops($dropzone, $user) : [];

                /* Unregisters user from unfinished drops associated to team he doesn't belong to anymore */
                foreach ($teamDrops as $teamDrop) {
                    if (!$teamDrop->isFinished() && !in_array($teamDrop->getTeamUuid(), $teamsIds)) {
                        /* Unregisters user from unfinished drop */
                        $this->dropzoneManager->unregisterUserFromTeamDrop($teamDrop, $user);
                    } else {
                        $drops[] = $teamDrop;
                    }
                }
                if (0 === count($drops)) {
                    /* Checks if there are unfinished drops from teams he belongs but not associated to him */
                    $unfinishedTeamsDrops = $this->dropzoneManager->getTeamsUnfinishedDrops($dropzone, $teamsIds);

                    if (count($unfinishedTeamsDrops) > 0) {
                        $errorMessage = $this->translator->trans('existing_unfinished_team_drop_error', [], 'dropzone');
                    }
                } elseif (1 === count($drops)) {
                    $myDrop = $drops[0];
                } else {
                    $errorMessage = $this->translator->trans('more_than_one_drop_error', [], 'dropzone');
                }
                if (!empty($myDrop)) {
                    $teamId = $myDrop->getTeamUuid();
                }
                $finishedPeerDrops = $this->dropzoneManager->getFinishedPeerDrops($dropzone, $user, $teamId);
                break;
        }
        $serializedTools = $this->dropzoneManager->getSerializedTools();
        /* TODO: generate ResourceUserEvaluation for team */
        $userEvaluation = !empty($user) ? $this->dropzoneManager->getResourceUserEvaluation($dropzone, $user) : null;
        $mySerializedDrop = !empty($myDrop) ? $this->serializer->serialize($myDrop) : null;
        $currentRevisionId = null;

        if ($mySerializedDrop && isset($mySerializedDrop['documents'][0]['revision']['id'])) {
            $currentRevisionId = $mySerializedDrop['documents'][0]['revision']['id'];
        }

        return [
            'dropzone' => $this->serializer->serialize($dropzone),
            'myDrop' => $mySerializedDrop,
            'nbCorrections' => count($finishedPeerDrops),
            'tools' => $serializedTools,
            'userEvaluation' => $this->serializer->serialize($userEvaluation, [Options::SERIALIZE_MINIMAL]),
            'teams' => $serializedTeams,
            'errorMessage' => $errorMessage,
            'currentRevisionId' => $currentRevisionId,
        ];
    }
}
