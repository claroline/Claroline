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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\DropZoneBundle\Entity\Revision;
use Claroline\DropZoneBundle\Entity\RevisionComment;

class RevisionCommentSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    private $revisionRepo;
    private $userRepo;

    /**
     * RevisionCommentSerializer constructor.
     */
    public function __construct(ObjectManager $om, SerializerProvider $serializer)
    {
        $this->serializer = $serializer;

        $this->revisionRepo = $om->getRepository(Revision::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    public function getName()
    {
        return 'dropzone_revision_comment';
    }

    /**
     * @return array
     */
    public function serialize(RevisionComment $comment, array $options = [])
    {
        $serialized = [
            'id' => $comment->getUuid(),
            'content' => $comment->getContent(),
            'meta' => [
                'user' => $comment->getUser() ?
                    $this->serializer->serialize($comment->getUser(), [Options::SERIALIZE_MINIMAL]) :
                    null,
                'creationDate' => DateNormalizer::normalize($comment->getCreationDate()),
                'editionDate' => $comment->getEditionDate() ?
                    DateNormalizer::normalize($comment->getEditionDate()) :
                    null,
            ],
        ];

        return $serialized;
    }

    /**
     * @param array $data
     *
     * @return RevisionComment
     */
    public function deserialize($data, RevisionComment $comment)
    {
        $this->sipe('id', 'setUuid', $data, $comment);
        $this->sipe('content', 'setContent', $data, $comment);

        if (!$comment->getUser() && isset($data['meta']['user']['id'])) {
            $user = $this->userRepo->findOneBy(['uuid' => $data['meta']['user']['id']]);
            $comment->setUser($user);
        }
        if (!$comment->getRevision() && isset($data['meta']['revision']['id'])) {
            $revision = $this->revisionRepo->findOneBy(['uuid' => $data['meta']['revision']['id']]);
            $comment->setRevision($revision);
        }

        return $comment;
    }
}
