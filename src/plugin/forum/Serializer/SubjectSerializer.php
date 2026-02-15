<?php

namespace Claroline\ForumBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SubjectSerializer
{
    use SerializerTrait;

    private ObjectRepository $messageRepo;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PublicFileSerializer $fileSerializer,
        private readonly ObjectManager $om,
        private readonly UserSerializer $userSerializer
    ) {
        $this->messageRepo = $om->getRepository(Message::class);
    }

    public function getClass(): string
    {
        return Subject::class;
    }

    public function getName(): string
    {
        return 'forum_subject';
    }

    public function getSchema(): string
    {
        return '#/plugin/forum/subject.json';
    }

    public function getSamples(): string
    {
        return '#/plugin/forum/subject';
    }

    public function serialize(Subject $subject, ?array $options = []): array
    {
        $first = $this->messageRepo->findOneBy([
            'subject' => $subject,
            'first' => true,
        ]);

        $count = $this->messageRepo->count([
            'subject' => $subject,
            'parent' => null,
            'first' => false,
        ]);

        $serialized = [
            'id' => $subject->getUuid(),
            'forum' => [
                'id' => $subject->getForum()->getUuid(),
            ],
            'poster' => $subject->getPoster()?->getUrl(),
            'title' => $subject->getTitle(),
            'content' => $first ? $first->getContent() : null,
            'tags' => $this->serializeTags($subject),
            'meta' => [
                'views' => $subject->getViewCount(),
                'messages' => $count,
                'creator' => !empty($subject->getCreator()) ? $this->userSerializer->serialize($subject->getCreator(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
                'created' => DateNormalizer::normalize($subject->getCreatedAt()),
                'updated' => DateNormalizer::normalize($subject->getUpdatedAt()),
                'sticky' => $subject->isSticked(),
                'closed' => $subject->isClosed(),
                'flagged' => $subject->isFlagged(),
            ],
        ];

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            $isAdmin = $this->authorization->isGranted('ADMINISTRATE', $subject);
            $serialized['permissions'] = [
                'open' => $isAdmin || $this->authorization->isGranted('OPEN', $subject),
                'edit' => $isAdmin || $this->authorization->isGranted('EDIT', $subject),
                'administrate' => $isAdmin,
                'delete' => $isAdmin || $this->authorization->isGranted('DELETE', $subject),
            ];
        }

        return $serialized;
    }

    /**
     * Deserializes data into a Subject entity.
     */
    public function deserialize(array $data, Subject $subject, array $options = []): Subject
    {
        $first = $this->messageRepo->findOneBy([
            'subject' => $subject,
            'first' => true,
        ]);

        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $subject);
        }

        $this->sipe('title', 'setTitle', $data, $subject);
        $this->sipe('meta.sticky', 'setSticked', $data, $subject);
        $this->sipe('meta.closed', 'setClosed', $data, $subject);
        $this->sipe('meta.flagged', 'setFlagged', $data, $subject);

        if (isset($data['content'])) {
            // TODO this should be done in the CRUD instead
            if (!$first) {
                $first = new Message();
                $first->setFirst(true);
                $first->setSubject($subject);
            }

            $first->setContent($data['content']);
            $this->om->persist($first);
        }

        if (isset($data['meta'])) {
            if (isset($data['meta']['created'])) {
                $subject->setCreatedAt(DateNormalizer::denormalize($data['meta']['updated']));
            }

            if (isset($data['meta']['updated'])) {
                $subject->setUpdatedAt(DateNormalizer::denormalize($data['meta']['updated']));
            }

            if (isset($data['meta']['creator'])) {
                $creator = $this->om->getObject($data['meta']['creator'], User::class);

                if ($creator) {
                    $subject->setCreator($creator);
                    if ($first) {
                        $first->setCreator($creator);
                    }
                }
            }
        }

        if (!empty($data['forum'])) {
            $forum = $this->om->getObject($data['forum'], Forum::class) ?? new Forum();

            if ($forum) {
                $subject->setForum($forum);
            }
        }

        if (array_key_exists('poster', $data)) {
            $poster = null;
            if (!empty($data['poster'])) {
                /** @var PublicFile $poster */
                $poster = $this->om->getRepository(PublicFile::class)->findOneBy([
                    'url' => $data['poster'],
                ]);
            }
            $subject->setPoster($poster);
        }

        if (isset($data['tags'])) {
            if (is_string($data['tags'])) {
                $this->deserializeTags($subject, explode(',', $data['tags']));
            } else {
                $this->deserializeTags($subject, $data['tags']);
            }
        }

        return $subject;
    }

    private function serializeTags(Subject $subject): array
    {
        $event = new GenericDataEvent([
            'class' => Subject::class,
            'ids' => [$subject->getUuid()],
        ]);
        $this->eventDispatcher->dispatch($event, 'claroline_retrieve_used_tags_by_class_and_ids');

        return $event->getResponse();
    }

    /**
     * Deserializes Item tags.
     */
    private function deserializeTags(Subject $subject, array $tags = [], array $options = []): void
    {
        $event = new GenericDataEvent([
            'tags' => $tags,
            'data' => [
                [
                    'class' => Subject::class,
                    'id' => $subject->getUuid(),
                    'name' => $subject->getTitle(),
                ],
            ],
            'replace' => true,
        ]);

        $this->eventDispatcher->dispatch($event, 'claroline_tag_multiple_data');
    }
}
