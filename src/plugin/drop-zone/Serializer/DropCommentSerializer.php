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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\DropComment;
use Doctrine\Persistence\ObjectRepository;

class DropCommentSerializer
{
    use SerializerTrait;

    private ObjectRepository $dropRepo;
    private ObjectRepository $userRepo;

    public function __construct(
        ObjectManager $om,
        private readonly UserSerializer $userSerializer
    ) {
        $this->dropRepo = $om->getRepository(Drop::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    public function getName(): string
    {
        return 'dropzone_drop_comment';
    }

    public function getClass(): string
    {
        return DropComment::class;
    }

    public function serialize(DropComment $comment): array
    {
        return [
            'id' => $comment->getUuid(),
            'content' => $comment->getContent(),
            'user' => $comment->getUser() ?
                $this->userSerializer->serialize($comment->getUser(), [Options::SERIALIZE_MINIMAL]) :
                null,
            'creationDate' => DateNormalizer::normalize($comment->getCreationDate()),
            'editionDate' => DateNormalizer::normalize($comment->getEditionDate()),
        ];
    }

    public function deserialize(array $data, DropComment $comment): DropComment
    {
        $this->sipe('id', 'setUuid', $data, $comment);
        $this->sipe('content', 'setContent', $data, $comment);

        if (!$comment->getUser() && isset($data['user']['id'])) {
            $user = $this->userRepo->findOneBy(['uuid' => $data['user']['id']]);
            $comment->setUser($user);
        }

        if (!$comment->getDrop() && isset($data['drop']['id'])) {
            $drop = $this->dropRepo->findOneBy(['uuid' => $data['drop']['id']]);
            $comment->setDrop($drop);
        }

        return $comment;
    }
}
