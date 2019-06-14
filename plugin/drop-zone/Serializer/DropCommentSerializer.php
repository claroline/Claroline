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
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\DropComment;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.dropzone.drop.comment")
 * @DI\Tag("claroline.serializer")
 */
class DropCommentSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    private $dropRepo;
    private $userRepo;

    /**
     * DropCommentSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(ObjectManager $om, SerializerProvider $serializer)
    {
        $this->serializer = $serializer;

        $this->dropRepo = $om->getRepository(Drop::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    /**
     * @param DropComment $comment
     * @param array       $options
     *
     * @return array
     */
    public function serialize(DropComment $comment, array $options = [])
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
     * @param array       $data
     * @param DropComment $comment
     *
     * @return DropComment
     */
    public function deserialize($data, DropComment $comment)
    {
        $this->sipe('id', 'setUuid', $data, $comment);
        $this->sipe('content', 'setContent', $data, $comment);

        if (!$comment->getUser() && isset($data['meta']['user']['id'])) {
            $user = $this->userRepo->findOneBy(['uuid' => $data['meta']['user']['id']]);
            $comment->setUser($user);
        }
        if (!$comment->getDrop() && isset($data['meta']['drop']['id'])) {
            $drop = $this->dropRepo->findOneBy(['uuid' => $data['meta']['drop']['id']]);
            $comment->setDrop($drop);
        }

        return $comment;
    }
}
