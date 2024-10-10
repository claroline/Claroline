<?php

namespace Claroline\ForumBundle\Serializer;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
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
use Claroline\ForumBundle\Manager\ForumManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SubjectSerializer
{
    use SerializerTrait;

    private ObjectRepository $messageRepo;

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

    public function __construct(
        private readonly FinderProvider $finder,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PublicFileSerializer $fileSerializer,
        private readonly ObjectManager $om,
        private readonly UserSerializer $userSerializer,
        private readonly ForumManager $manager
    ) {
        $this->messageRepo = $om->getRepository(Message::class);
    }

    /**
     * Serializes a Subject entity.
     */
    public function serialize(Subject $subject, ?array $options = []): array
    {
        $first = $this->messageRepo->findOneBy([
          'subject' => $subject,
          'first' => true,
        ]);

        return [
            'id' => $subject->getUuid(),
            'forum' => [
                'id' => $subject->getForum()->getUuid(),
            ],
            'tags' => $this->serializeTags($subject),
            'content' => $first ? $first->getContent() : null,
            'title' => $subject->getTitle(),
            'meta' => [
                'moderation' => $subject->getModerated(),
                'views' => $subject->getViewCount(),
                // don't use Finder in a Serializer
                'messages' => $this->finder->fetch(Message::class, ['subject' => $subject->getUuid(), 'parent' => null], null, 0, 0, true),
                'creator' => !empty($subject->getCreator()) ? $this->userSerializer->serialize($subject->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
                'created' => DateNormalizer::normalize($subject->getCreationDate()),
                'updated' => DateNormalizer::normalize($subject->getModificationDate()),
                'sticky' => $subject->isSticked(),
                'closed' => $subject->isClosed(),
                'flagged' => $subject->isFlagged(),
            ],
            'poster' => $subject->getPoster() ? $subject->getPoster()->getUrl() : null,
        ];
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

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $subject);
        }

        $this->sipe('title', 'setTitle', $data, $subject);
        $this->sipe('meta.sticky', 'setSticked', $data, $subject);
        $this->sipe('meta.closed', 'setClosed', $data, $subject);
        $this->sipe('meta.flagged', 'setFlagged', $data, $subject);
        $this->sipe('meta.moderation', 'setModerated', $data, $subject);

        if (isset($data['content'])) {
            // TODO this should be done in the CRUD instead
            if (!$first) {
                $first = new Message();
                $first->setFirst(true);
                $first->setSubject($subject);
                $first->setModerated($subject->getModerated());
            }

            $first->setContent($data['content']);
        }

        if (isset($data['meta'])) {
            if (isset($data['meta']['updated'])) {
                $subject->setModificationDate(DateNormalizer::denormalize($data['meta']['updated']));
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

        if ($first) {
            $this->om->persist($first);
        }

        return $subject;
    }

    private function serializeTags(Subject $subject)
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
    private function deserializeTags(Subject $subject, array $tags = [], array $options = [])
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
