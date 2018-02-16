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

use Claroline\CoreBundle\API\Crud;
use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\DropZoneBundle\Entity\Correction;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Entity\DropzoneTool;
use Claroline\DropZoneBundle\Entity\DropzoneToolDocument;
use Claroline\DropZoneBundle\Repository\CorrectionRepository;
use Claroline\DropZoneBundle\Repository\DropRepository;
use Claroline\DropZoneBundle\Serializer\CorrectionSerializer;
use Claroline\DropZoneBundle\Serializer\DocumentSerializer;
use Claroline\DropZoneBundle\Serializer\DropSerializer;
use Claroline\DropZoneBundle\Serializer\DropzoneSerializer;
use Claroline\DropZoneBundle\Serializer\DropzoneToolSerializer;
use Claroline\TeamBundle\Entity\Team;
use JMS\DiExtraBundle\Annotation as DI;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @DI\Service("claroline.manager.dropzone_manager")
 */
class DropzoneManager
{
    /** @var Crud */
    private $crud;

    /** @var DropzoneSerializer */
    private $dropzoneSerializer;

    /** @var DropSerializer */
    private $dropSerializer;

    /** @var DocumentSerializer */
    private $documentSerializer;

    /** @var CorrectionSerializer */
    private $correctionSerializer;

    /** @var DropzoneToolSerializer */
    private $dropzoneToolSerializer;

    /** @var Filesystem */
    private $fileSystem;

    private $filesDir;

    /** @var ObjectManager */
    private $om;

    /**
     * @var ResourceEvaluationManager
     */
    private $resourceEvalManager;

    private $archiveDir;
    private $configHandler;

    /** @var DropRepository */
    private $dropRepo;

    /** @var CorrectionRepository */
    private $correctionRepo;
    private $dropzoneToolRepo;
    private $dropzoneToolDocumentRepo;

    /**
     * DropzoneManager constructor.
     *
     * @DI\InjectParams({
     *     "crud"                   = @DI\Inject("claroline.api.crud"),
     *     "dropzoneSerializer"     = @DI\Inject("claroline.serializer.dropzone"),
     *     "dropSerializer"         = @DI\Inject("claroline.serializer.dropzone.drop"),
     *     "documentSerializer"     = @DI\Inject("claroline.serializer.dropzone.document"),
     *     "correctionSerializer"   = @DI\Inject("claroline.serializer.dropzone.correction"),
     *     "dropzoneToolSerializer" = @DI\Inject("claroline.serializer.dropzone.tool"),
     *     "fileSystem"             = @DI\Inject("filesystem"),
     *     "filesDir"               = @DI\Inject("%claroline.param.files_directory%"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "resourceEvalManager"    = @DI\Inject("claroline.manager.resource_evaluation_manager"),
     *     "archiveDir"             = @DI\Inject("%claroline.param.platform_generated_archive_path%"),
     *     "configHandler"          = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * @param Crud                         $crud
     * @param DropzoneSerializer           $dropzoneSerializer
     * @param DropSerializer               $dropSerializer
     * @param DocumentSerializer           $documentSerializer
     * @param CorrectionSerializer         $correctionSerializer
     * @param DropzoneToolSerializer       $dropzoneToolSerializer
     * @param Filesystem                   $fileSystem
     * @param string                       $filesDir
     * @param ObjectManager                $om
     * @param ResourceEvaluationManager    $resourceEvalManager
     * @param string                       $archiveDir
     * @param PlatformConfigurationHandler $configHandler
     */
    public function __construct(
        Crud $crud,
        DropzoneSerializer $dropzoneSerializer,
        DropSerializer $dropSerializer,
        DocumentSerializer $documentSerializer,
        CorrectionSerializer $correctionSerializer,
        DropzoneToolSerializer $dropzoneToolSerializer,
        Filesystem $fileSystem,
        $filesDir,
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager,
        $archiveDir,
        PlatformConfigurationHandler $configHandler
    ) {
        $this->crud = $crud;
        $this->dropzoneSerializer = $dropzoneSerializer;
        $this->dropSerializer = $dropSerializer;
        $this->documentSerializer = $documentSerializer;
        $this->correctionSerializer = $correctionSerializer;
        $this->dropzoneToolSerializer = $dropzoneToolSerializer;
        $this->fileSystem = $fileSystem;
        $this->filesDir = $filesDir;
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->archiveDir = $archiveDir;
        $this->configHandler = $configHandler;

        $this->dropRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Drop');
        $this->correctionRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Correction');
        $this->dropzoneToolRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\DropzoneTool');
        $this->dropzoneToolDocumentRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\DropzoneToolDocument');
    }

    /**
     * Serializes a Dropzone entity.
     *
     * @param Dropzone $dropzone
     *
     * @return array
     */
    public function serialize(Dropzone $dropzone)
    {
        return $this->dropzoneSerializer->serialize($dropzone);
    }

    /**
     * Serializes a Drop entity.
     *
     * @param Drop $drop
     *
     * @return array
     */
    public function serializeDrop(Drop $drop)
    {
        return $this->dropSerializer->serialize($drop);
    }

    /**
     * Serializes a Document entity.
     *
     * @param Document $document
     *
     * @return array
     */
    public function serializeDocument(Document $document)
    {
        return $this->documentSerializer->serialize($document);
    }

    /**
     * Serializes a Correction entity.
     *
     * @param Correction $correction
     *
     * @return array
     */
    public function serializeCorrection(Correction $correction)
    {
        return $this->correctionSerializer->serialize($correction);
    }

    /**
     * Serializes a Tool entity.
     *
     * @param DropzoneTool $tool
     *
     * @return array
     */
    public function serializeTool(DropzoneTool $tool)
    {
        return $this->dropzoneToolSerializer->serialize($tool);
    }

    /**
     * Updates a Dropzone.
     *
     * @param Dropzone $dropzone
     * @param array    $data
     *
     * @return Dropzone
     */
    public function update(Dropzone $dropzone, array $data)
    {
        $this->crud->update('Claroline\DropZoneBundle\Entity\Dropzone', $data);

        return $dropzone;
    }

    /**
     * Deletes a Dropzone.
     *
     * @param Dropzone $dropzone
     */
    public function delete(Dropzone $dropzone)
    {
        $this->om->startFlushSuite();
        $uuid = $dropzone->getUuid();
        $ds = DIRECTORY_SEPARATOR;
        $dropzoneDir = $this->filesDir.$ds.'dropzone'.$ds.$uuid;

        if ($this->fileSystem->exists($dropzoneDir)) {
            $this->fileSystem->remove($dropzoneDir);
        }
        $this->crud->delete($dropzone);
        $this->om->endFlushSuite();
    }

    /**
     * Sets Dropzone drop type to default.
     *
     * @param Dropzone $dropzone
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
     * @param Dropzone $dropzone
     *
     * @return array
     */
    public function getAllDrops(Dropzone $dropzone)
    {
        return $this->dropRepo->findBy(['dropzone' => $dropzone]);
    }

    /**
     * Gets user drop or creates one.
     *
     * @param Dropzone $dropzone
     * @param User     $user
     * @param bool     $withCreation
     *
     * @return Drop
     */
    public function getUserDrop(Dropzone $dropzone, User $user, $withCreation = false)
    {
        $drops = $this->dropRepo->findBy(['dropzone' => $dropzone, 'user' => $user, 'teamId' => null]);
        $drop = count($drops) > 0 ? $drops[0] : null;

        if (empty($drop) && $withCreation) {
            $this->om->startFlushSuite();
            $drop = new Drop();
            $drop->setUser($user);
            $drop->setDropzone($dropzone);
            $this->om->persist($drop);
            $this->generateResourceEvaluation($dropzone, $user, AbstractResourceEvaluation::STATUS_INCOMPLETE);
            $this->om->endFlushSuite();
        }

        return $drop;
    }

    /**
     * Gets team drop or creates one.
     *
     * @param Dropzone $dropzone
     * @param Team     $team
     * @param User     $user
     * @param bool     $withCreation
     *
     * @return Drop
     */
    public function getTeamDrop(Dropzone $dropzone, Team $team, User $user, $withCreation = false)
    {
        $drop = $this->dropRepo->findOneBy(['dropzone' => $dropzone, 'teamId' => $team->getId()]);

        if ($withCreation) {
            if (empty($drop)) {
                $this->om->startFlushSuite();
                $drop = new Drop();
                $drop->setUser($user);
                $drop->setDropzone($dropzone);
                $drop->setTeamId($team->getId());
                $drop->setTeamName($team->getName());

                foreach ($team->getUsers() as $teamUser) {
                    $drop->addUser($teamUser);
                    /* TODO: checks that a valid status is not overwritten */
                    $this->generateResourceEvaluation($dropzone, $teamUser, AbstractResourceEvaluation::STATUS_INCOMPLETE);
                }
                $this->om->persist($drop);
                $this->om->endFlushSuite();
            } elseif (!$drop->hasUser($user)) {
                $this->om->startFlushSuite();
                $drop->addUser($user);
                $this->generateResourceEvaluation($dropzone, $user, AbstractResourceEvaluation::STATUS_INCOMPLETE);
                $this->om->persist($drop);
                $this->om->endFlushSuite();
            }
        }

        return $drop;
    }

    /**
     * Gets Team drops or create one.
     *
     * @param Dropzone $dropzone
     * @param User     $user
     *
     * @return array
     */
    public function getTeamDrops(Dropzone $dropzone, User $user)
    {
        $drops = $this->dropRepo->findTeamDrops($dropzone, $user);

        return $drops;
    }

    /**
     * Deletes a Drop.
     *
     * @param Drop $drop
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
     * @param Dropzone $dropzone
     * @param User     $user
     *
     * @return int|null
     */
    public function getUserTeamId(Dropzone $dropzone, User $user)
    {
        $teamId = null;

        if ($dropzone->getDropType() === Dropzone::DROP_TYPE_TEAM) {
            $teamDrops = $this->getTeamDrops($dropzone, $user);

            if (count($teamDrops) === 1) {
                $teamId = $teamDrops[0]->getTeamId();
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
     * @param Drop  $drop
     * @param User  $user
     * @param int   $documentType
     * @param mixed $documentData
     *
     * @return Document
     */
    public function createDocument(Drop $drop, User $user, $documentType, $documentData)
    {
        $document = new Document();
        $document->setDrop($drop);
        $document->setUser($user);
        $document->setDropDate(new \DateTime());
        $document->setType($documentType);
        $document->setData($documentData);
        $this->om->persist($document);
        $this->om->flush();

        return $document;
    }

    /**
     * Creates Files Documents.
     *
     * @param Drop  $drop
     * @param User  $user
     * @param array $files
     *
     * @return array
     */
    public function createFilesDocuments(Drop $drop, User $user, array $files)
    {
        $documents = [];
        $currentDate = new \DateTime();
        $dropzone = $drop->getDropzone();
        $this->om->startFlushSuite();

        foreach ($files as $file) {
            $document = new Document();
            $document->setDrop($drop);
            $document->setUser($user);
            $document->setDropDate($currentDate);
            $document->setType(Document::DOCUMENT_TYPE_FILE);
            $data = $this->registerUplodadedFile($dropzone, $file);
            $document->setFile($data);
            $this->om->persist($document);
            $documents[] = $this->serializeDocument($document);
        }
        $this->om->endFlushSuite();

        return $documents;
    }

    /**
     * Deletes a Document.
     *
     * @param Document $document
     */
    public function deleteDocument(Document $document)
    {
        if ($document->getType() === Document::DOCUMENT_TYPE_FILE) {
            $data = $document->getFile();

            if (isset($data['url'])) {
                $this->fileSystem->remove($this->filesDir.DIRECTORY_SEPARATOR.$data['url']);
            }
        }
        $this->om->remove($document);
        $this->om->flush();
    }

    /**
     * Terminates a drop.
     *
     * @param Drop $drop
     * @param User $user
     */
    public function submitDrop(Drop $drop, User $user)
    {
        $this->om->startFlushSuite();

        $drop->setFinished(true);
        $drop->setDropDate(new \DateTime());

        if ($drop->getTeamId()) {
            $drop->setUser($user);
        }
        $users = $drop->getTeamId() ? $drop->getUsers() : [$drop->getUser()];
        $this->om->persist($drop);
        $this->checkCompletion($drop->getDropzone(), $users, $drop);

        $this->om->endFlushSuite();
    }

    /**
     * Computes Drop score from submitted Corrections.
     *
     * @param Drop $drop
     *
     * @return Drop
     */
    public function computeDropScore(Drop $drop)
    {
        $corrections = $drop->getCorrections();
        $score = 0;
        $nbValidCorrection = 0;

        foreach ($corrections as $correction) {
            if ($correction->isFinished() && $correction->isValid()) {
                $score += $correction->getScore();
                ++$nbValidCorrection;
            }
        }
        $score = $nbValidCorrection > 0 ? round($score / $nbValidCorrection, 2) : null;
        $drop->setScore($score);
        $this->om->persist($drop);
        $this->om->flush();

        return $drop;
    }

    /**
     * Unlocks Drop.
     *
     * @param Drop $drop
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
     * @param Drop $drop
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
     * @param Drop $drop
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
     *
     * @param Dropzone $dropzone
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
     * @param array $data
     * @param User  $user
     *
     * @return Correction
     */
    public function saveCorrection(array $data, User $user)
    {
        $this->om->startFlushSuite();
        $existingCorrection = $this->correctionRepo->findOneBy(['uuid' => $data['id']]);
        $isNew = empty($existingCorrection);
        $correction = $this->correctionSerializer->deserialize('Claroline\DropZoneBundle\Entity\Correction', $data);
        $correction->setUser($user);

        if (!$isNew) {
            $correction->setLastEditionDate(new \DateTime());
        }
        $correction = $this->computeCorrectionScore($correction);
        $this->om->persist($correction);
        $this->om->endFlushSuite();

        return $correction;
    }

    /**
     * Submits a Correction.
     *
     * @param Correction $correction
     * @param User       $user
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
        $users = [];

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                $users = [$user];
                break;
            case Dropzone::DROP_TYPE_TEAM:
                $teamDrops = $this->getTeamDrops($dropzone, $user);

                if (count($teamDrops) === 1) {
                    $users = $teamDrops[0]->getUsers();
                }
                break;
        }
        $this->checkCompletion($drop->getDropzone(), $users);
        $this->checkSuccess($drop);

        $this->om->endFlushSuite();

        return $correction;
    }

    /**
     * Switch Correction validation.
     *
     * @param Correction $correction
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

        return $correction;
    }

    /**
     * Deletes a Correction.
     *
     * @param Correction $correction
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
    }

    /**
     * Denies a Correction.
     *
     * @param Correction $correction
     * @param string     $comment
     *
     * @return Correction
     */
    public function denyCorrection(Correction $correction, $comment = null)
    {
        $correction->setCorrectionDenied(true);
        $correction->setCorrectionDeniedComment($comment);
        $this->om->persist($correction);
        $this->om->flush();

        return $correction;
    }

    /**
     * Computes Correction score from criteria grades.
     *
     * @param Correction $correction
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

    public function getSerializedTools()
    {
        $serializedTools = [];
        $tools = $this->dropzoneToolRepo->findAll();

        foreach ($tools as $tool) {
            $serializedTools[] = $this->dropzoneToolSerializer->serialize($tool);
        }

        return $serializedTools;
    }

    /**
     * Updates a Tool.
     *
     * @param array $data
     *
     * @return Tool
     */
    public function saveTool(array $data)
    {
        $tool = $this->dropzoneToolSerializer->deserialize('Claroline\DropZoneBundle\Entity\DropzoneTool', $data);
        $this->om->persist($tool);
        $this->om->flush();

        return $tool;
    }

    /**
     * Deletes a Tool.
     *
     * @param DropzoneTool $tool
     */
    public function deleteTool(DropzoneTool $tool)
    {
        $this->om->remove($tool);
        $this->om->flush();
    }

    /**
     * Gets unifnished drops from teams list.
     *
     * @param Dropzone $dropzone
     * @param array    $teamsIds
     *
     * @return array
     */
    public function getTeamsUnfinishedDrops(Dropzone $dropzone, array $teamsIds)
    {
        return $this->dropRepo->findTeamsUnfinishedDrops($dropzone, $teamsIds);
    }

    /**
     * Gets user|team drop if it is finished.
     *
     * @param Dropzone $dropzone
     * @param User     $user
     * @param int      $teamId
     *
     * @return array
     */
    public function getFinishedUserDrop(Dropzone $dropzone, User $user = null, $teamId = null)
    {
        $drop = null;

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                if (!empty($user)) {
                    $drop = $this->dropRepo->findOneBy([
                        'dropzone' => $dropzone,
                        'user' => $user,
                        'teamId' => null,
                        'finished' => true,
                    ]);
                }
                break;
            case Dropzone::DROP_TYPE_TEAM:
                if ($teamId) {
                    $drop = $this->dropRepo->findOneBy(['dropzone' => $dropzone, 'teamId' => $teamId, 'finished' => true]);
                }
                break;
        }

        return $drop;
    }

    /**
     * Gets drops corrected by user|team.
     *
     * @param Dropzone $dropzone
     * @param User     $user
     * @param int      $teamId
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
     * @param Dropzone $dropzone
     * @param User     $user
     * @param int      $teamId
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
     * @param Dropzone $dropzone
     * @param User     $user
     * @param int      $teamId
     * @param string   $teamName
     * @param bool     $withCreation
     *
     * @return Drop | null
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
     * @param Dropzone $dropzone
     * @param User     $user
     * @param int      $teamId
     * @param string   $teamName
     *
     * @return Drop | null
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
            $correction->setTeamId($teamId);
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
     * Executes a Tool on a Document.
     *
     * @param DropzoneTool $tool
     * @param Document     $document
     *
     * @return Document
     */
    public function executeTool(DropzoneTool $tool, Document $document)
    {
        if ($tool->getType() === DropzoneTool::COMPILATIO && $document->getType() === Document::DOCUMENT_TYPE_FILE) {
            $toolDocument = $this->dropzoneToolDocumentRepo->findOneBy(['tool' => $tool, 'document' => $document]);
            $toolData = $tool->getData();
            $compilatio = new \SoapClient($toolData['url']);

            if (empty($toolDocument)) {
                $documentData = $document->getFile();
                $params = [];
                $params[] = $toolData['key'];
                $params[] = utf8_encode($documentData['name']);
                $params[] = utf8_encode($documentData['name']);
                $params[] = utf8_encode($documentData['name']);
                $params[] = utf8_encode($documentData['mimeType']);
                $params[] = base64_encode(file_get_contents($this->filesDir.DIRECTORY_SEPARATOR.$documentData['url']));
                $idDocument = $compilatio->__call('addDocumentBase64', $params);
                $analysisParams = [];
                $analysisParams[] = $toolData['key'];
                $analysisParams[] = $idDocument;
                $compilatio->__call('startDocumentAnalyse', $analysisParams);
                $reportUrl = $compilatio->__call('getDocumentReportUrl', $analysisParams);

                if ($idDocument && $reportUrl) {
                    $this->createToolDocument($tool, $document, $idDocument, $reportUrl);
                }
            }
        }

        return $document;
    }

    /**
     * Associates data generated by a Tool to a Document.
     *
     * @param DropzoneTool $tool
     * @param Document     $document
     * @param string       $idDocument
     * @param string       $reportUrl
     */
    public function createToolDocument(DropzoneTool $tool, Document $document, $idDocument = null, $reportUrl = null)
    {
        $toolDocument = new DropzoneToolDocument();
        $toolDocument->setTool($tool);
        $toolDocument->setDocument($document);
        $data = ['idDocument' => $idDocument, 'reportUrl' => $reportUrl];
        $toolDocument->setData($data);
        $this->om->persist($toolDocument);
        $this->om->flush();
    }

    /**
     * Computes Complete status for a user.
     *
     * @param Dropzone $dropzone
     * @param array    $users
     * @param Drop     $drop
     */
    public function checkCompletion(Dropzone $dropzone, array $users, Drop $drop = null)
    {
        $fixedStatusList = [
            AbstractResourceEvaluation::STATUS_COMPLETED,
            AbstractResourceEvaluation::STATUS_PASSED,
            AbstractResourceEvaluation::STATUS_FAILED,
        ];
        $teamId = !empty($drop) ? $drop->getTeamId() : null;

        $this->om->startFlushSuite();

        /* By default drop is complete if teacher review is enabled or drop is unlocked for user */
        $isComplete = !empty($drop) ? $drop->isFinished() && (!$dropzone->isPeerReview() || $drop->isUnlockedUser()) : false;

        /* If drop is not complete by default, checks for the number of finished corrections done by user */
        if (!$isComplete) {
            $expectedCorrectionTotal = $dropzone->getExpectedCorrectionTotal();
            $finishedPeerDrops = $this->getFinishedPeerDrops($dropzone, $users[0], $teamId);
            $isComplete = count($finishedPeerDrops) >= $expectedCorrectionTotal;
        }
        if ($isComplete) {
            foreach ($users as $user) {
                $userEval = $this->resourceEvalManager->getResourceUserEvaluation($dropzone->getResourceNode(), $user, false);

                if (!empty($userEval) && !in_array($userEval->getStatus(), $fixedStatusList)) {
                    $this->generateResourceEvaluation($dropzone, $user, AbstractResourceEvaluation::STATUS_COMPLETED);
                }
            }
        }
        $this->om->endFlushSuite();
    }

    /**
     * Computes Success status for a Drop.
     *
     * @param Drop $drop
     */
    public function checkSuccess(Drop $drop)
    {
        $this->om->startFlushSuite();

        $dropzone = $drop->getDropzone();
        $users = [$drop->getUser()];

        if ($dropzone->getDropType() === Dropzone::DROP_TYPE_TEAM) {
            $users = $drop->getUsers();
        }
        $computeStatus = $drop->isFinished() && (!$dropzone->isPeerReview() || $drop->isUnlockedDrop());

        if (!$computeStatus) {
            $nbValidCorrections = 0;
            $expectedCorrectionTotal = $dropzone->getExpectedCorrectionTotal();
            $corrections = $drop->getCorrections();

            foreach ($corrections as $correction) {
                if ($correction->isFinished() && $correction->isValid()) {
                    ++$nbValidCorrections;
                }
            }
            $computeStatus = $nbValidCorrections >= $expectedCorrectionTotal;
        }
        if ($computeStatus) {
            $score = $drop->getScore();
            $scoreToPass = $dropzone->getScoreToPass();
            $status = $score >= $scoreToPass ? AbstractResourceEvaluation::STATUS_PASSED : AbstractResourceEvaluation::STATUS_FAILED;

            foreach ($users as $user) {
                $this->generateResourceEvaluation($dropzone, $user, $status, $score, $drop, true);
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * Retrieves ResourceUserEvaluation for a Dropzone and an user or creates one.
     *
     * @param Dropzone $dropzone
     * @param User     $user
     *
     * @return ResourceUserEvaluation
     */
    public function generateResourceUserEvaluation(Dropzone $dropzone, User $user)
    {
        $userEval = $this->resourceEvalManager->getResourceUserEvaluation($dropzone->getResourceNode(), $user, false);

        if (empty($userEval)) {
            $userEval = $this->generateResourceEvaluation($dropzone, $user, AbstractResourceEvaluation::STATUS_NOT_ATTEMPTED);
        }

        return $userEval;
    }

    /**
     * Creates a ResourceEvaluation for a Dropzone and an user.
     *
     * @param Dropzone $dropzone
     * @param User     $user
     * @param string   $status
     * @param float    $score
     * @param Drop     $drop
     * @param bool     $forceStatus
     *
     * @return ResourceUserEvaluation
     */
    public function generateResourceEvaluation(
        Dropzone $dropzone,
        User $user,
        $status,
        $score = null,
        Drop $drop = null,
        $forceStatus = false
    ) {
        $data = !empty($drop) ? $this->serializeDrop($drop) : null;

        $this->resourceEvalManager->createResourceEvaluation(
            $dropzone->getResourceNode(),
            $user,
            new \DateTime(),
            $status,
            $score,
            null,
            $dropzone->getScoreMax(),
            null,
            null,
            null,
            $data,
            $forceStatus
        );
    }

    /**
     * Retrieves all corrections made for a Dropzone.
     *
     * @param Dropzone $dropzone
     *
     * @return array
     */
    public function getAllCorrectionsData(Dropzone $dropzone)
    {
        $data = [];
        $corrections = $this->correctionRepo->findAllCorrectionsByDropzone($dropzone);

        foreach ($corrections as $correction) {
            $teamId = $correction->getTeamId();
            $key = empty($teamId) ? 'user_'.$correction->getUser()->getUuid() : 'team_'.$teamId;

            if (!isset($data[$key])) {
                $data[$key] = [];
            }
            $data[$key][] = $this->serializeCorrection($correction);
        }

        return $data;
    }

    /**
     * @param array $drops
     *
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
                strtolower($drop->getUser()->getFirstName().' '.$drop->getUser()->getLastName().' - '.$drop->getUser()->getUsername());

            if ($date !== '') {
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
        file_put_contents($this->archiveDir, $pathArch."\n", FILE_APPEND);

        return $pathArch;
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
}
