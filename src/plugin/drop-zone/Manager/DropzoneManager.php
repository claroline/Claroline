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

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository;
use Claroline\DropZoneBundle\Entity\Correction;
use Claroline\DropZoneBundle\Entity\Criterion;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Entity\Revision;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionDeleteEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionEndEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionReportEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionStartEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionUpdateEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionValidationChangeEvent;
use Claroline\DropZoneBundle\Event\Log\LogDocumentCreateEvent;
use Claroline\DropZoneBundle\Event\Log\LogDocumentDeleteEvent;
use Claroline\DropZoneBundle\Event\Log\LogDropEndEvent;
use Claroline\DropZoneBundle\Repository\CorrectionRepository;
use Claroline\DropZoneBundle\Repository\DocumentRepository;
use Claroline\DropZoneBundle\Repository\DropRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DropzoneManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var RoleManager */
    private $roleManager;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Filesystem */
    private $fileSystem;
    /** @var string */
    private $filesDir;
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $configHandler;

    /** @var ResourceNodeRepository */
    private $resourceNodeRepo;
    /** @var DropRepository */
    private $dropRepo;
    /** @var CorrectionRepository */
    private $correctionRepo;
    /** @var DocumentRepository */
    private $documentRepo;

    public function __construct(
        SerializerProvider $serializer,
        Filesystem $fileSystem,
        string $filesDir,
        ObjectManager $om,
        PlatformConfigurationHandler $configHandler,
        EventDispatcherInterface $eventDispatcher,
        RoleManager $roleManager
    ) {
        $this->serializer = $serializer;
        $this->fileSystem = $fileSystem;
        $this->filesDir = $filesDir;
        $this->om = $om;
        $this->configHandler = $configHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->roleManager = $roleManager;

        $this->dropRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Drop');
        $this->correctionRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Correction');
        $this->documentRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Document');
        $this->resourceNodeRepo = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceNode');
    }

    /**
     * Serializes a Dropzone entity.
     *
     * @return array
     */
    public function serialize(Dropzone $dropzone)
    {
        return $this->serializer->serialize($dropzone);
    }

    /**
     * Serializes a Drop entity.
     *
     * @return array
     */
    public function serializeDrop(Drop $drop)
    {
        return $this->serializer->serialize($drop);
    }

    /**
     * Serializes a Document entity.
     *
     * @return array
     */
    public function serializeDocument(Document $document)
    {
        return $this->serializer->serialize($document);
    }

    /**
     * Serializes a Correction entity.
     *
     * @return array
     */
    public function serializeCorrection(Correction $correction)
    {
        return $this->serializer->serialize($correction);
    }

    /**
     * Serializes a Revision entity.
     *
     * @return array
     */
    public function serializeRevision(Revision $revision)
    {
        return $this->serializer->serialize($revision);
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
     * Gets all drops for given Dropzone.
     *
     * @return array
     */
    public function getAllDrops(Dropzone $dropzone)
    {
        return $this->dropRepo->findBy(['dropzone' => $dropzone]);
    }

    /**
     * Gets Team drops or create one.
     *
     * @return array
     */
    public function getTeamDrops(Dropzone $dropzone, User $user)
    {
        return $this->dropRepo->findTeamDrops($dropzone, $user);
    }

    /**
     * Deletes a Drop.
     */
    public function deleteDrop(Drop $drop)
    {
        $this->om->startFlushSuite();
        $documents = $drop->getDocuments();

        foreach ($documents as $document) {
            $this->deleteDocument($document);
        }
        $this->om->remove($drop);
        $this->om->endFlushSuite();
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
            $teamDrops = $this->getTeamDrops($dropzone, $user);

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
     * Creates a Document.
     *
     * @param mixed    $documentData
     * @param Revision $revision
     * @param bool     $isManager
     *
     * @return Document
     */
    public function createDocument(Drop $drop, User $user, string $documentType, $documentData, Revision $revision = null, $isManager = false)
    {
        $document = new Document();
        $document->setDrop($drop);
        $document->setUser($user);
        $document->setDropDate(new \DateTime());
        $document->setType($documentType);
        $document->setRevision($revision);
        $document->setIsManager($isManager);

        if (Document::DOCUMENT_TYPE_RESOURCE === $document->getType()) {
            $resourceNode = $this->resourceNodeRepo->findOneBy(['uuid' => $documentData['id']]);
            $document->setData($resourceNode);
        } else {
            $document->setData($documentData);
        }

        $this->om->persist($document);
        $this->om->flush();

        $this->eventDispatcher->dispatch(new LogDocumentCreateEvent($drop->getDropzone(), $drop, $document), 'log');

        return $document;
    }

    /**
     * Creates Files Documents.
     *
     * @param Revision $revision
     * @param bool     $isManager
     *
     * @return array
     */
    public function createFilesDocuments(Drop $drop, User $user, array $files, Revision $revision = null, $isManager = false)
    {
        $documents = [];
        $documentEntities = [];
        $currentDate = new \DateTime();
        $dropzone = $drop->getDropzone();
        $this->om->startFlushSuite();

        foreach ($files as $file) {
            $document = new Document();
            $document->setDrop($drop);
            $document->setUser($user);
            $document->setDropDate($currentDate);
            $document->setType(Document::DOCUMENT_TYPE_FILE);
            $document->setRevision($revision);
            $document->setIsManager($isManager);
            $data = $this->registerUplodadedFile($dropzone, $file);
            $document->setFile($data);
            $this->om->persist($document);
            $documentEntities[] = $document;
            $documents[] = $this->serializeDocument($document);
        }
        $this->om->endFlushSuite();

        //tracking for each document, after flush
        foreach ($documentEntities as $entity) {
            $this->eventDispatcher->dispatch(new LogDocumentCreateEvent($drop->getDropzone(), $drop, $entity), 'log');
        }

        return $documents;
    }

    /**
     * Deletes a Document.
     */
    public function deleteDocument(Document $document)
    {
        if (Document::DOCUMENT_TYPE_FILE === $document->getType()) {
            $data = $document->getFile();

            if (isset($data['url'])) {
                $this->fileSystem->remove($this->filesDir.DIRECTORY_SEPARATOR.$data['url']);
            }
        }
        $this->om->remove($document);
        $this->om->flush();

        $this->eventDispatcher->dispatch(new LogDocumentDeleteEvent($document->getDrop()->getDropzone(), $document->getDrop(), $document), 'log');
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
        $this->checkCompletion($drop->getDropzone(), $users, $drop);

        $this->om->endFlushSuite();

        $this->eventDispatcher->dispatch(new LogDropEndEvent($drop->getDropzone(), $drop, $this->roleManager), 'log');
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
        $this->checkSuccess($drop);
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
        $this->checkCompletion($dropzone, $users, $drop);
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
     * Updates a Correction.
     *
     * @return Correction
     */
    public function saveCorrection(array $data, User $user)
    {
        $this->om->startFlushSuite();
        $existingCorrection = $this->correctionRepo->findOneBy(['uuid' => $data['id']]);
        $isNew = empty($existingCorrection);
        $correction = $this->serializer->get(Correction::class)->deserialize($data);
        $correction->setUser($user);
        $dropzone = $correction->getDrop()->getDropzone();

        if (!$isNew) {
            $correction->setLastEditionDate(new \DateTime());
        }
        $correction = $this->computeCorrectionScore($correction);
        $this->om->persist($correction);
        $this->om->endFlushSuite();

        if ($isNew) {
            $this->eventDispatcher->dispatch(new LogCorrectionStartEvent($dropzone, $correction->getDrop(), $correction), 'log');
        } else {
            $this->eventDispatcher->dispatch(new LogCorrectionUpdateEvent($dropzone, $correction->getDrop(), $correction), 'log');
        }

        return $correction;
    }

    /**
     * Submits a Correction.
     *
     * @return Correction
     */
    public function submitCorrection(Correction $correction, User $user)
    {
        $this->om->startFlushSuite();

        $correction->setFinished(true);
        $correction->setEndDate(new \DateTime());
        $correction->setUser($user);
        $this->om->persist($correction);
        $this->om->forceFlush();
        $drop = $this->computeDropScore($correction->getDrop());
        $dropzone = $drop->getDropzone();
        $userDrop = null;
        $users = [];

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                $users = [$user];
                $userDrop = $this->getUserDrop($dropzone, $user);
                break;
            case Dropzone::DROP_TYPE_TEAM:
                $teamDrops = $this->getTeamDrops($dropzone, $user);

                if (1 === count($teamDrops)) {
                    $users = $teamDrops[0]->getUsers();
                    $userDrop = $teamDrops[0];
                }
                break;
        }
        $this->eventDispatcher->dispatch(new LogCorrectionEndEvent($dropzone, $correction->getDrop(), $correction), 'log');
        $this->om->forceFlush();

        $this->checkSuccess($drop);
        $this->checkCompletion($dropzone, $users, $userDrop);

        $this->om->endFlushSuite();

        return $correction;
    }

    /**
     * Switch Correction validation.
     *
     * @return Correction
     */
    public function switchCorrectionValidation(Correction $correction)
    {
        $this->om->startFlushSuite();

        $correction->setValid(!$correction->isValid());
        $this->om->persist($correction);
        $drop = $this->computeDropScore($correction->getDrop());
        $this->checkSuccess($drop);

        $this->om->endFlushSuite();

        $this->eventDispatcher->dispatch(new LogCorrectionValidationChangeEvent($correction->getDrop()->getDropzone(), $correction->getDrop(), $correction), 'log');

        return $correction;
    }

    /**
     * Deletes a Correction.
     */
    public function deleteCorrection(Correction $correction)
    {
        $this->om->startFlushSuite();

        $drop = $correction->getDrop();
        $drop->removeCorrection($correction);
        $this->om->remove($correction);
        $drop = $this->computeDropScore($drop);
        $this->checkSuccess($drop);

        $this->om->endFlushSuite();

        $this->eventDispatcher->dispatch(new LogCorrectionDeleteEvent($correction->getDrop()->getDropzone(), $drop, $correction), 'log');
    }

    /**
     * Denies a Correction.
     *
     * @param string $comment
     *
     * @return Correction
     */
    public function denyCorrection(Correction $correction, $comment = null)
    {
        $correction->setCorrectionDenied(true);
        $correction->setCorrectionDeniedComment($comment);
        $this->om->persist($correction);
        $this->om->flush();

        $this->eventDispatcher->dispatch(new LogCorrectionReportEvent($correction->getDrop()->getDropzone(), $correction->getDrop(), $correction, $this->roleManager), 'log');

        return $correction;
    }

    /**
     * Computes Correction score from criteria grades.
     *
     * @return Correction
     */
    public function computeCorrectionScore(Correction $correction)
    {
        $drop = $correction->getDrop();
        $dropzone = $drop->getDropzone();
        $criteria = $dropzone->getCriteria();

        if ($dropzone->isCriteriaEnabled() && count($criteria) > 0) {
            $score = 0;
            $criteriaIds = [];
            $scoreMax = $dropzone->getScoreMax();
            $total = ($dropzone->getCriteriaTotal() - 1) * count($criteria);
            $grades = $correction->getGrades();

            foreach ($criteria as $criterion) {
                $criteriaIds[] = $criterion->getUuid();
            }
            foreach ($grades as $grade) {
                $gradeCriterion = $grade->getCriterion();

                if (in_array($gradeCriterion->getUuid(), $criteriaIds)) {
                    $score += $grade->getValue();
                }
            }
            $score = round(($score / $total) * $scoreMax, 2);
            $correction->setScore($score);
        }
        $this->om->persist($correction);
        $this->om->flush();

        return $correction;
    }

    /**
     * Gets unifnished drops from teams list.
     *
     * @return array
     */
    public function getTeamsUnfinishedDrops(Dropzone $dropzone, array $teamsIds)
    {
        return $this->dropRepo->findTeamsUnfinishedDrops($dropzone, $teamsIds);
    }

    /**
     * Gets user|team drop if it is finished.
     */
    public function getFinishedUserDrop(Dropzone $dropzone, ?User $user = null, ?string $teamId = null): Drop
    {
        $drop = null;

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                if (!empty($user)) {
                    $drop = $this->dropRepo->findOneBy([
                        'dropzone' => $dropzone,
                        'user' => $user,
                        'teamUuid' => null,
                        'finished' => true,
                    ]);
                }
                break;
            case Dropzone::DROP_TYPE_TEAM:
                if ($teamId) {
                    $drop = $this->dropRepo->findOneBy(['dropzone' => $dropzone, 'teamUuid' => $teamId, 'finished' => true]);
                }
                break;
        }

        return $drop;
    }

    /**
     * Gets drops corrected by user|team.
     *
     * @param User   $user
     * @param string $teamId
     *
     * @return array
     */
    public function getFinishedPeerDrops(Dropzone $dropzone, User $user = null, $teamId = null)
    {
        $drops = [];

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                if (!empty($user)) {
                    $drops = $this->dropRepo->findUserFinishedPeerDrops($dropzone, $user);
                }
                break;
            case Dropzone::DROP_TYPE_TEAM:
                if ($teamId) {
                    $drops = $this->dropRepo->findTeamFinishedPeerDrops($dropzone, $teamId);
                }
                break;
        }

        return $drops;
    }

    /**
     * Gets drops corrected by user|team but that are not finished.
     *
     * @param User   $user
     * @param string $teamId
     *
     * @return array
     */
    public function getUnfinishedPeerDrops(Dropzone $dropzone, User $user = null, $teamId = null)
    {
        $drops = [];

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                if (!empty($user)) {
                    $drops = $this->dropRepo->findUserUnfinishedPeerDrop($dropzone, $user);
                }
                break;
            case Dropzone::DROP_TYPE_TEAM:
                if ($teamId) {
                    $drops = $this->dropRepo->findTeamUnfinishedPeerDrop($dropzone, $teamId);
                }
                break;
        }

        return $drops;
    }

    /**
     * Gets a drop for peer evaluation.
     *
     * @param User   $user
     * @param string $teamId
     * @param string $teamName
     * @param bool   $withCreation
     *
     * @return Drop|null
     */
    public function getPeerDrop(Dropzone $dropzone, User $user = null, $teamId = null, $teamName = null, $withCreation = true)
    {
        $peerDrop = null;

        /* Gets user|team drop to check if it is finished before allowing peer review */
        $userDrop = $this->getFinishedUserDrop($dropzone, $user, $teamId);

        /* user|team drop is finished */
        if (!empty($userDrop)) {
            /* Gets drops where user|team has an unfinished correction */
            $unfinishedDrops = $this->getUnfinishedPeerDrops($dropzone, $user, $teamId);

            if (count($unfinishedDrops) > 0) {
                /* Returns the first drop with an unfinished correction */
                $peerDrop = $unfinishedDrops[0];
            } else {
                /* Gets drops where user|team has a finished correction */
                $finishedDrops = $this->getFinishedPeerDrops($dropzone, $user, $teamId);
                $nbCorrections = count($finishedDrops);

                /* Fetches a drop for peer correction if user|team has not made the expected number of corrections */
                if ($withCreation && $dropzone->isReviewEnabled() && $nbCorrections < $dropzone->getExpectedCorrectionTotal()) {
                    $peerDrop = $this->getAvailableDropForPeer($dropzone, $user, $teamId, $teamName);
                }
            }
        }

        return $peerDrop;
    }

    /**
     * Gets available drop for peer evaluation.
     *
     * @param User   $user
     * @param string $teamId
     * @param string $teamName
     *
     * @return Drop|null
     */
    public function getAvailableDropForPeer(Dropzone $dropzone, User $user = null, $teamId = null, $teamName = null)
    {
        $peerDrop = null;
        $drops = [];

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                if (!empty($user)) {
                    $drops = $this->dropRepo->findUserAvailableDrops($dropzone, $user);
                }
                break;
            case Dropzone::DROP_TYPE_TEAM:
                if ($teamId) {
                    $drops = $this->dropRepo->findTeamAvailableDrops($dropzone, $teamId);
                }
                break;
        }
        $validDrops = [];

        foreach ($drops as $drop) {
            $corrections = $drop->getCorrections();

            if (count($corrections) < $dropzone->getExpectedCorrectionTotal()) {
                $validDrops[] = $drop;
            }
        }
        if (count($validDrops) > 0) {
            /* Selects the drop with the least corrections */
            $peerDrop = $this->getDropWithTheLeastCorrections($validDrops);

            /* Creates empty correction */
            $correction = new Correction();
            $correction->setDrop($peerDrop);
            $correction->setUser($user);
            $correction->setTeamUuid($teamId);
            $correction->setTeamName($teamName);
            $currentDate = new \DateTime();
            $correction->setStartDate($currentDate);
            $correction->setLastEditionDate($currentDate);
            $peerDrop->addCorrection($correction);
            $this->om->persist($correction);
            $this->om->flush();
        }

        return $peerDrop;
    }

    /**
     * Retrieves all corrections made for a Dropzone.
     *
     * @return array
     */
    public function getAllCorrectionsData(Dropzone $dropzone)
    {
        $data = [];
        $corrections = $this->correctionRepo->findAllCorrectionsByDropzone($dropzone);

        foreach ($corrections as $correction) {
            $teamId = $correction->getTeamUuid();
            $key = empty($teamId) ? 'user_'.$correction->getUser()->getUuid() : 'team_'.$teamId;

            if (!isset($data[$key])) {
                $data[$key] = [];
            }
            $data[$key][] = $this->serializeCorrection($correction);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function generateArchiveForDrops(array $drops)
    {
        $ds = DIRECTORY_SEPARATOR;
        $archive = new \ZipArchive();
        $pathArch = $this->configHandler->getParameter('tmp_dir').$ds.Uuid::uuid4()->toString().'.zip';
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
                        $textPath = $this->configHandler->getParameter('tmp_dir').$ds.$name;
                        file_put_contents($textPath, $document->getData());
                        $archive->addFile(
                            $textPath,
                            $dirName.$ds.$name
                        );
                        break;
                    case Document::DOCUMENT_TYPE_URL:
                        $name = 'url_'.Uuid::uuid4()->toString();
                        $textPath = $this->configHandler->getParameter('tmp_dir').$ds.$name;
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

    /**
     * Copy a Dropzone resource.
     *
     * @return Dropzone
     */
    public function copyDropzone(Dropzone $dropzone, Dropzone $newDropzone)
    {
        foreach ($dropzone->getCriteria() as $criterion) {
            $newCriterion = new Criterion();
            $newCriterion->setDropzone($newDropzone);
            $newCriterion->setInstruction($criterion->getInstruction());
            $this->om->persist($newCriterion);
        }

        return $newDropzone;
    }

    private function getDropWithTheLeastCorrections(array $drops)
    {
        $selectedDrop = count($drops) > 0 ? $drops[0] : null;
        $min = !empty($selectedDrop) ? count($selectedDrop->getCorrections()) : null;

        foreach ($drops as $drop) {
            $nbCorrections = count($drop->getCorrections());

            if ($nbCorrections < $min) {
                $selectedDrop = $drop;
                $min = $nbCorrections;
            }
        }

        return $selectedDrop;
    }

    private function registerUplodadedFile(Dropzone $dropzone, UploadedFile $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $hashName = Uuid::uuid4()->toString();
        $dir = $this->filesDir.$ds.'dropzone'.$ds.$dropzone->getUuid();
        $fileName = $hashName.'.'.$file->getClientOriginalExtension();

        $file->move($dir, $fileName);

        return [
            'name' => $file->getClientOriginalName(),
            'mimeType' => $file->getClientMimeType(),
            'url' => 'dropzone'.$ds.$dropzone->getUuid().$ds.$fileName,
        ];
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
