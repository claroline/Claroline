<?php

namespace Claroline\AudioPlayerBundle\Serializer\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AudioPlayerBundle\Entity\Resource\Section;
use Claroline\AudioPlayerBundle\Entity\Resource\SectionComment;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Doctrine\Persistence\ObjectRepository;

class SectionCommentSerializer
{
    use SerializerTrait;

    private ObjectRepository $sectionRepo;
    private ObjectRepository $userRepo;

    public function __construct(
        ObjectManager $om,
        private readonly SectionSerializer $sectionSerializer,
        private readonly UserSerializer $userSerializer
    ) {
        $this->sectionRepo = $om->getRepository(Section::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    public function getClass(): string
    {
        return SectionComment::class;
    }

    public function serialize(SectionComment $sectionComment, array $options = []): array
    {
        return [
            'id' => $sectionComment->getUuid(),
            'content' => $sectionComment->getContent(),
            'meta' => [
                'creationDate' => DateNormalizer::normalize($sectionComment->getCreationDate()),
                'editionDate' => $sectionComment->getEditionDate() ?
                    DateNormalizer::normalize($sectionComment->getEditionDate()) :
                    null,
                'user' => $sectionComment->getUser() ?
                    $this->userSerializer->serialize($sectionComment->getUser(), [Options::SERIALIZE_MINIMAL]) :
                    null,
                'section' => $this->sectionSerializer->serialize($sectionComment->getSection()),
            ],
        ];
    }

    public function deserialize(array $data, SectionComment $sectionComment, array $options = []): SectionComment
    {
        $this->sipe('content', 'setContent', $data, $sectionComment);

        if (isset($data['meta']['user']['id']) && !$sectionComment->getUser()) {
            $user = $this->userRepo->findOneBy(['uuid' => $data['meta']['user']['id']]);

            if ($user) {
                $sectionComment->setUser($user);
            }
        }
        if (isset($data['meta']['section']['id']) && !$sectionComment->getSection()) {
            $section = $this->sectionRepo->findOneBy(['uuid' => $data['meta']['section']['id']]);

            if ($section) {
                $sectionComment->setSection($section);
            }
        }

        return $sectionComment;
    }
}
