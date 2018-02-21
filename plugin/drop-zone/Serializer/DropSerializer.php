<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.dropzone.drop")
 * @DI\Tag("claroline.serializer")
 */
class DropSerializer
{
    private $correctionSerializer;
    private $documentSerializer;
    private $userSerializer;

    private $dropRepo;
    private $dropzoneRepo;
    private $userRepo;

    /**
     * DropSerializer constructor.
     *
     * @DI\InjectParams({
     *     "correctionSerializer" = @DI\Inject("claroline.serializer.dropzone.correction"),
     *     "documentSerializer"   = @DI\Inject("claroline.serializer.dropzone.document"),
     *     "userSerializer"       = @DI\Inject("claroline.serializer.user"),
     *     "om"                   = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param CorrectionSerializer $correctionSerializer
     * @param DocumentSerializer   $documentSerializer
     * @param UserSerializer       $userSerializer
     * @param ObjectManager        $om
     */
    public function __construct(
        CorrectionSerializer $correctionSerializer,
        DocumentSerializer $documentSerializer,
        UserSerializer $userSerializer,
        ObjectManager $om
    ) {
        $this->correctionSerializer = $correctionSerializer;
        $this->documentSerializer = $documentSerializer;
        $this->userSerializer = $userSerializer;

        $this->dropRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Drop');
        $this->dropzoneRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Dropzone');
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
    }

    /**
     * @param Drop $drop
     *
     * @return array
     */
    public function serialize(Drop $drop)
    {
        return [
            'id' => $drop->getUuid(),
            'user' => $drop->getUser() ? $this->userSerializer->serialize($drop->getUser()) : null,
            'dropDate' => $drop->getDropDate() ? $drop->getDropDate()->format('Y-m-d H:i') : null,
            'score' => $drop->getScore(),
            'reported' => $drop->isReported(),
            'finished' => $drop->isFinished(),
            'autoClosedDrop' => $drop->getAutoClosedDrop(),
            'unlockedDrop' => $drop->isUnlockedDrop(),
            'unlockedUser' => $drop->isUnlockedUser(),
            'teamId' => $drop->getTeamId(),
            'teamName' => $drop->getTeamName(),
            'documents' => $this->getDocuments($drop),
            'corrections' => $this->getCorrections($drop),
            'users' => $this->getUsers($drop),
        ];
    }

    /**
     * @param string $class
     * @param array  $data
     *
     * @return Drop
     */
    public function deserialize($class, $data)
    {
        $drop = $this->dropRepo->findOneBy(['uuid' => $data['id']]);

        if (empty($drop)) {
            $drop = new Drop();
            $drop->setUuid($data['id']);
            /** @var Dropzone $dropzone */
            $dropzone = $this->dropzoneRepo->findOneBy(['uuid' => $data['drop']]);
            $drop->setDropzone($dropzone);
        }
        if (isset($data['user'])) {
            $user = isset($data['user']['id']) ? $this->userRepo->findOneBy(['id' => $data['user']['id']]) : null;
            $drop->setUser($user);
        }
        if (isset($data['dropDate'])) {
            $dropDate = !empty($data['dropDate']) ? new \DateTime($data['dropDate']) : null;
            $drop->setDropDate($dropDate);
        }
        if (isset($data['score'])) {
            $drop->setScore($data['score']);
        }
        if (isset($data['reported'])) {
            $drop->setReported($data['reported']);
        }
        if (isset($data['finished'])) {
            $drop->setFinished($data['finished']);
        }
        if (isset($data['autoClosedDrop'])) {
            $drop->setAutoClosedDrop($data['autoClosedDrop']);
        }
        if (isset($data['unlockedDrop'])) {
            $drop->setUnlockedDrop($data['unlockedDrop']);
        }
        if (isset($data['unlockedUser'])) {
            $drop->setUnlockedUser($data['unlockedUser']);
        }
        if (isset($data['teamId'])) {
            $drop->setTeamId($data['teamId']);
        }
        if (isset($data['teamName'])) {
            $drop->setTeamName($data['teamName']);
        }
        $this->deserializeDocuments($drop, $data['documents']);
        $this->deserializeCorrections($drop, $data['corrections']);
        $this->deserializeUsers($drop, $data['users']);

        return $drop;
    }

    private function getDocuments(Drop $drop)
    {
        $documents = [];

        foreach ($drop->getDocuments() as $document) {
            $documents[] = $this->documentSerializer->serialize($document);
        }

        return $documents;
    }

    private function getCorrections(Drop $drop)
    {
        $corrections = [];

        foreach ($drop->getCorrections() as $correction) {
            $corrections[] = $this->correctionSerializer->serialize($correction);
        }

        return $corrections;
    }

    private function getUsers(Drop $drop)
    {
        $users = [];

        foreach ($drop->getUsers() as $user) {
            $users[] = $this->userSerializer->serialize($user);
        }

        return $users;
    }

    private function deserializeDocuments(Drop $drop, $documentsData)
    {
        $drop->emptyDocuments();

        foreach ($documentsData as $documentData) {
            $document = $this->documentSerializer->deserialize('Claroline\DropZoneBundle\Entity\Document', $documentData);
            $drop->addDocument($document);
        }
    }

    private function deserializeCorrections(Drop $drop, $correctionsData)
    {
        $drop->emptyCorrections();

        foreach ($correctionsData as $correctionData) {
            $correction = $this->correctionSerializer->deserialize('Claroline\DropZoneBundle\Entity\Correction', $correctionData);
            $drop->addCorrection($correction);
        }
    }

    private function deserializeUsers(Drop $drop, $usersData)
    {
        $drop->emptyUsers();

        foreach ($usersData as $userData) {
            $user = $this->userRepo->findOneBy(['id' => $userData['id']]);

            if (!empty($user)) {
                $drop->addUser($user);
            }
        }
    }
}
