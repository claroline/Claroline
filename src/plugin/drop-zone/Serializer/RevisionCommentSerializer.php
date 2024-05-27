<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\DropZoneBundle\Entity\Revision;
use Claroline\DropZoneBundle\Entity\RevisionComment;
use Doctrine\Persistence\ObjectRepository;

class RevisionCommentSerializer
{
    use SerializerTrait;

    private ObjectRepository $revisionRepo;
    private ObjectRepository $userRepo;

    public function __construct(
        ObjectManager $om,
        private readonly UserSerializer $userSerializer
    ) {
        $this->revisionRepo = $om->getRepository(Revision::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    public function getName(): string
    {
        return 'dropzone_revision_comment';
    }

    public function getClass(): string
    {
        return RevisionComment::class;
    }

    public function serialize(RevisionComment $comment): array
    {
        return [
            'id' => $comment->getUuid(),
            'content' => $comment->getContent(),
            'user' => $comment->getUser() ?
                $this->userSerializer->serialize($comment->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                null,
            'creationDate' => DateNormalizer::normalize($comment->getCreationDate()),
            'editionDate' => DateNormalizer::normalize($comment->getEditionDate()),
        ];
    }

    public function deserialize(array $data, RevisionComment $comment): RevisionComment
    {
        $this->sipe('id', 'setUuid', $data, $comment);
        $this->sipe('content', 'setContent', $data, $comment);

        if (!$comment->getUser() && isset($data['user']['id'])) {
            $user = $this->userRepo->findOneBy(['uuid' => $data['user']['id']]);
            $comment->setUser($user);
        }

        if (!$comment->getRevision() && isset($data['revision']['id'])) {
            $revision = $this->revisionRepo->findOneBy(['uuid' => $data['revision']['id']]);
            $comment->setRevision($revision);
        }

        return $comment;
    }
}
