<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\DropComment;
use Claroline\DropZoneBundle\Entity\Dropzone;

class DropSerializer
{
    private $correctionSerializer;
    private $documentSerializer;
    private $dropCommentSerializer;
    private $userSerializer;

    private $dropRepo;
    private $dropzoneRepo;
    private $userRepo;

    public function __construct(
        CorrectionSerializer $correctionSerializer,
        DocumentSerializer $documentSerializer,
        DropCommentSerializer $dropCommentSerializer,
        UserSerializer $userSerializer,
        ObjectManager $om
    ) {
        $this->correctionSerializer = $correctionSerializer;
        $this->documentSerializer = $documentSerializer;
        $this->dropCommentSerializer = $dropCommentSerializer;
        $this->userSerializer = $userSerializer;

        $this->dropRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Drop');
        $this->dropzoneRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Dropzone');
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
    }

    public function getName()
    {
        return 'dropzone_drop';
    }

    public function serialize(Drop $drop): array
    {
        return [
            'id' => $drop->getUuid(),
            'user' => $drop->getUser() ? $this->userSerializer->serialize($drop->getUser()) : null,
            'dropDate' => $drop->getDropDate() ? $drop->getDropDate()->format('Y-m-d H:i') : null,
            'score' => $drop->getScore(),
            'finished' => $drop->isFinished(),
            'autoClosedDrop' => $drop->getAutoClosedDrop(),
            'unlockedDrop' => $drop->isUnlockedDrop(),
            'unlockedUser' => $drop->isUnlockedUser(),
            'teamId' => $drop->getTeamUuid(),
            'teamName' => $drop->getTeamName(),
            'documents' => $this->getDocuments($drop),
            'corrections' => $this->getCorrections($drop),
            'users' => $this->getUsers($drop),
            'comments' => $this->getComments($drop),
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
            $drop->setTeamUuid($data['teamId']);
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
        $revisionDocuments = [];

        $revisions = $drop->getRevisions()->toArray();
        $latestRevisionId = 0 < count($revisions) ? $revisions[0]->getUuid() : null;

        foreach ($drop->getDocuments() as $document) {
            $docRevision = $document->getRevision();

            if (!$docRevision) {
                $documents[] = $this->documentSerializer->serialize($document);
            } elseif ($latestRevisionId === $docRevision->getUuid()) {
                $revisionDocuments[] = $this->documentSerializer->serialize($document);
            }
        }

        return 0 < count($documents) ? $documents : $revisionDocuments;
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

    private function getComments(Drop $drop)
    {
        return array_values(array_map(function (DropComment $comment) {
            return $this->dropCommentSerializer->serialize($comment);
        }, $drop->getComments()->toArray()));
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
