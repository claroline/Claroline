<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Manager;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\DropZoneBundle\Entity\Criterion;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Entity\Revision;
use Claroline\DropZoneBundle\Event\Log\LogDropEndEvent;
use Claroline\DropZoneBundle\Repository\CorrectionRepository;
use Claroline\DropZoneBundle\Repository\DropRepository;
use Claroline\TeamBundle\Manager\TeamManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DropzoneManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var TranslatorInterface */
    private $translator;
    /** @var string */
    private $filesDir;
    /** @var ObjectManager */
    private $om;
    /** @var TempFileManager */
    private $tempManager;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TeamManager */
    private $teamManager;
    /** @var EvaluationManager */
    private $evaluationManager;
    /** @var DropManager */
    private $dropManager;

    /** @var DropRepository */
    private $dropRepo;
    /** @var CorrectionRepository */
    private $correctionRepo;

    public function __construct(
        string $filesDir,
        ObjectManager $om,
        TempFileManager $tempManager,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        SerializerProvider $serializer,
        TeamManager $teamManager,
        EvaluationManager $evaluationManager,
        DropManager $dropManager
    ) {
        $this->filesDir = $filesDir;
        $this->om = $om;
        $this->tempManager = $tempManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->serializer = $serializer;
        $this->teamManager = $teamManager;
        $this->evaluationManager = $evaluationManager;
        $this->dropManager = $dropManager;

        $this->dropRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Drop');
        $this->correctionRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Correction');
    }

    public function getDropzoneData(Dropzone $dropzone, ?User $user = null)
    {
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
                $this->closeAllUnfinishedDrops($dropzone);
            }
        }

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                $myDrop = !empty($user) ? $this->evaluationManager->getUserDrop($dropzone, $user) : null;
                $finishedPeerDrops = $this->dropManager->getFinishedPeerDrops($dropzone, $user);
                break;
            case Dropzone::DROP_TYPE_TEAM:
                $drops = [];
                $teamsIds = array_map(function ($team) {
                    return $team['id'];
                }, $serializedTeams);

                /* Fetches team drops associated to user */
                $teamDrops = !empty($user) ? $this->dropManager->getTeamDrops($dropzone, $user) : [];

                /* Unregisters user from unfinished drops associated to team he doesn't belong to anymore */
                foreach ($teamDrops as $teamDrop) {
                    if (!$teamDrop->isFinished() && !in_array($teamDrop->getTeamUuid(), $teamsIds)) {
                        /* Unregisters user from unfinished drop */
                        $this->unregisterUserFromTeamDrop($teamDrop, $user);
                    } else {
                        $drops[] = $teamDrop;
                    }
                }
                if (0 === count($drops)) {
                    /* Checks if there are unfinished drops from teams he belongs but not associated to him */
                    $unfinishedTeamsDrops = $this->dropManager->getTeamsUnfinishedDrops($dropzone, $teamsIds);

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
                $finishedPeerDrops = $this->dropManager->getFinishedPeerDrops($dropzone, $user, $teamId);
                break;
        }

        /* TODO: generate ResourceUserEvaluation for team */
        $userEvaluation = !empty($user) ? $this->evaluationManager->getResourceUserEvaluation($dropzone, $user) : null;
        $mySerializedDrop = !empty($myDrop) ? $this->serializer->serialize($myDrop) : null;
        $currentRevisionId = null;

        if ($mySerializedDrop && isset($mySerializedDrop['documents'][0]['revision']['id'])) {
            $currentRevisionId = $mySerializedDrop['documents'][0]['revision']['id'];
        }

        return [
            'dropzone' => $this->serializer->serialize($dropzone),
            'myDrop' => $mySerializedDrop,
            'nbCorrections' => count($finishedPeerDrops),
            'userEvaluation' => $this->serializer->serialize($userEvaluation, [Options::SERIALIZE_MINIMAL]),
            'teams' => $serializedTeams,
            'errorMessage' => $errorMessage,
            'currentRevisionId' => $currentRevisionId,
        ];
    }

    /**
     * Sets Dropzone drop type to default.
     */
    public function setDefaultDropType(Dropzone $dropzone)
    {
        $dropzone->setDropType(Dropzone::DROP_TYPE_USER);

        $this->om->persist($dropzone);
        $this->om->flush();
    }

    /**
     * Retrieves teamId of user.
     *
     * @return string|null
     */
    public function getUserTeamId(Dropzone $dropzone, User $user)
    {
        $teamId = null;

        if (Dropzone::DROP_TYPE_TEAM === $dropzone->getDropType()) {
            $teamDrops = $this->dropManager->getTeamDrops($dropzone, $user);

            if (1 === count($teamDrops)) {
                $teamId = $teamDrops[0]->getTeamUuid();
            }
        }

        return $teamId;
    }

    public function unregisterUserFromTeamDrop(Drop $drop, User $user)
    {
        $drop->removeUser($user);
        $this->om->persist($drop);
        $this->om->flush();
    }

    /**
     * Terminates a drop.
     */
    public function submitDrop(Drop $drop, User $user)
    {
        $this->om->startFlushSuite();

        $drop->setFinished(true);
        $drop->setDropDate(new \DateTime());

        if ($drop->getTeamUuid()) {
            $drop->setUser($user);
        }
        $users = $drop->getTeamUuid() ? $drop->getUsers() : [$drop->getUser()];
        $this->om->persist($drop);
        $this->evaluationManager->checkCompletion($drop->getDropzone(), $users, $drop);

        $this->om->endFlushSuite();

        $this->eventDispatcher->dispatch(new LogDropEndEvent($drop->getDropzone(), $drop), 'log');
    }

    /**
     * Creates a revision for drop.
     */
    public function submitDropForRevision(Drop $drop, User $user)
    {
        $revision = new Revision();
        $revision->setDrop($drop);
        $revision->setCreator($user);

        foreach ($drop->getDocuments() as $document) {
            if (!$document->getRevision()) {
                $document->setRevision($revision);
                $this->om->persist($document);
            }
        }
        $this->om->persist($revision);
        $this->om->persist($drop);
        $this->om->flush();

        return $revision;
    }

    /**
     * Unlocks Drop.
     *
     * @return Drop
     */
    public function unlockDrop(Drop $drop)
    {
        $this->om->startFlushSuite();

        $drop->setUnlockedDrop(true);
        $this->evaluationManager->checkSuccess($drop);
        $this->om->persist($drop);

        $this->om->endFlushSuite();

        return $drop;
    }

    /**
     * Unlocks Drop user.
     *
     * @return Drop
     */
    public function unlockDropUser(Drop $drop)
    {
        $this->om->startFlushSuite();

        $drop->setUnlockedUser(true);
        $dropzone = $drop->getDropzone();
        $users = [];

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                $users = [$drop->getUser()];
                break;
            case Dropzone::DROP_TYPE_TEAM:
                $users = $drop->getUsers();
                break;
        }
        $this->evaluationManager->checkCompletion($dropzone, $users, $drop);
        $this->om->persist($drop);

        $this->om->endFlushSuite();

        return $drop;
    }

    /**
     * Cancels Drop submission.
     *
     * @return Drop
     */
    public function cancelDropSubmission(Drop $drop)
    {
        $drop->setFinished(false);
        $drop->setDropDate(null);
        /* TODO: cancels completion */
        $this->om->persist($drop);
        $this->om->flush();

        return $drop;
    }

    /**
     * Closes all unfinished drops.
     */
    public function closeAllUnfinishedDrops(Dropzone $dropzone)
    {
        $this->om->startFlushSuite();

        $currentDate = new \DateTime();
        $drops = $this->dropRepo->findBy(['dropzone' => $dropzone, 'finished' => false]);

        /** @var Drop $drop */
        foreach ($drops as $drop) {
            $drop->setFinished(true);
            $drop->setDropDate($currentDate);
            $drop->setAutoClosedDrop(true);
            $this->om->persist($drop);
        }
        $dropzone->setDropClosed(true);
        $this->om->persist($dropzone);

        $this->om->endFlushSuite();
    }

    /**
     * @return string
     */
    public function generateArchiveForDrops(array $drops)
    {
        $ds = DIRECTORY_SEPARATOR;
        $archive = new \ZipArchive();
        $pathArch = $this->tempManager->generate();
        $archive->open($pathArch, \ZipArchive::CREATE);

        foreach ($drops as $drop) {
            $date = $drop->isFinished() && !empty($drop->getDropDate()) ?
                $drop->getDropDate()->format('d-m-Y H:i:s') :
                '';
            $dirName = $drop->getTeamName() ?
                strtolower($drop->getTeamName()) :
                strtolower($drop->getUser()->getLastName().' '.$drop->getUser()->getFirstName().' - '.$drop->getUser()->getUsername());

            if ('' !== $date) {
                $dirName .= ' '.$date;
            }

            foreach ($drop->getDocuments() as $document) {
                switch ($document->getType()) {
                    case Document::DOCUMENT_TYPE_FILE:
                        $data = $document->getData();
                        $filePath = $this->filesDir.$ds.$data['url'];
                        $archive->addFile(
                            $filePath,
                            $dirName.$ds.$data['name']
                        );
                        break;
                    case Document::DOCUMENT_TYPE_TEXT:
                        $name = 'text_'.Uuid::uuid4()->toString().'.html';
                        $textPath = $this->tempManager->generate();
                        file_put_contents($textPath, $document->getData());
                        $archive->addFile(
                            $textPath,
                            $dirName.$ds.$name
                        );
                        break;
                    case Document::DOCUMENT_TYPE_URL:
                        $name = 'url_'.Uuid::uuid4()->toString();
                        $textPath = $this->tempManager->generate();
                        file_put_contents($textPath, $document->getData());
                        $archive->addFile(
                            $textPath,
                            $dirName.$ds.$name
                        );
                        break;
                }
            }
        }
        $archive->close();

        return $pathArch;
    }

    public function copyDropzone(Dropzone $dropzone, Dropzone $newDropzone): Dropzone
    {
        foreach ($dropzone->getCriteria() as $criterion) {
            $newCriterion = new Criterion();
            $newCriterion->setDropzone($newDropzone);
            $newCriterion->setInstruction($criterion->getInstruction());
            $this->om->persist($newCriterion);
        }

        return $newDropzone;
    }

    /**
     * Fetches all drops and corrections and updates their score depending on new score max.
     *
     * @param float $oldScoreMax
     * @param float $newScoreMax
     */
    public function updateScoreByScoreMax(Dropzone $dropzone, $oldScoreMax, $newScoreMax)
    {
        $ratio = !empty($oldScoreMax) && !empty($newScoreMax) ? $newScoreMax / $oldScoreMax : 0;

        if ($ratio) {
            $drops = $this->dropRepo->findBy(['dropzone' => $dropzone]);
            $corrections = $this->correctionRepo->findAllCorrectionsByDropzone($dropzone);
            $i = 0;

            $this->om->startFlushSuite();

            foreach ($drops as $drop) {
                $score = $drop->getScore();

                if ($score) {
                    $newScore = round($score * $ratio, 2);
                    $drop->setScore($newScore);
                    $this->om->persist($drop);
                }
                ++$i;

                if (0 === 200 % $i) {
                    $this->om->forceFlush();
                }
            }
            foreach ($corrections as $correction) {
                $score = $correction->getScore();

                if ($score) {
                    $newScore = round($score * $ratio, 2);
                    $correction->setScore($newScore);
                    $this->om->persist($correction);
                }
                ++$i;

                if (0 === 200 % $i) {
                    $this->om->forceFlush();
                }
            }
            $this->om->endFlushSuite();
        }
    }
}
