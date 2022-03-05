<?php

namespace Claroline\ForumBundle\Serializer;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
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

    /** @var FinderProvider */
    private $finder;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var PublicFileSerializer */
    private $fileSerializer;
    /** @var UserSerializer */
    private $userSerializer;
    /** @var ForumManager */
    private $manager;
    /** @var ObjectRepository */
    private $messageRepo;

    public function getClass()
    {
        return Subject::class;
    }

    public function getName()
    {
        return 'forum_subject';
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/forum/subject.json';
    }

    /**
     * @return string
     */
    public function getSamples()
    {
        return '#/plugin/forum/subject';
    }

    public function __construct(
        FinderProvider $finder,
        EventDispatcherInterface $eventDispatcher,
        PublicFileSerializer $fileSerializer,
        ObjectManager $om,
        UserSerializer $userSerializer,
        ForumManager $manager
    ) {
        $this->finder = $finder;
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
        $this->userSerializer = $userSerializer;
        $this->manager = $manager;

        $this->messageRepo = $om->getRepository(Message::class);
    }

    /**
     * Serializes a Subject entity.
     *
     * @return array
     */
    public function serialize(Subject $subject, array $options = [])
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
            'meta' => $this->serializeMeta($subject, $options),
            'poster' => $subject->getPoster() ? $this->fileSerializer->serialize($subject->getPoster()) : null,
        ];
    }

    private function serializeMeta(Subject $subject, array $options = [])
    {
        return [
            'moderation' => $subject->getModerated(),
            'views' => $subject->getViewCount(),
            'messages' => $this->finder->fetch(Message::class, ['subject' => $subject->getUuid(), 'parent' => null], null, 0, 0, true),
            'creator' => !empty($subject->getCreator()) ? $this->userSerializer->serialize($subject->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
            'created' => DateNormalizer::normalize($subject->getCreationDate()),
            'updated' => DateNormalizer::normalize($subject->getModificationDate()),
            'sticky' => $subject->isSticked(),
            'closed' => $subject->isClosed(),
            'flagged' => $subject->isFlagged(),
            'hot' => $this->isHot($subject),
        ];
    }

    /**
     * Deserializes data into a Subject entity.
     *
     * @param array $data
     *
     * @return Subject
     */
    public function deserialize($data, Subject $subject, array $options = [])
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
            // this should be done in the CRUD instead
            if (!$first) {
                $messageData = ['content' => $data['content']];

                if (isset($data['meta']) && isset($data['meta']['creator'])) {
                    $messageData['meta']['creator'] = $data['meta']['creator'];
                }

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
                $subject->setAuthor($data['meta']['creator']['name']);

                // TODO: reuse value from token Storage if new
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
                $poster = $this->om->getObject($data['poster'], PublicFile::class);
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

    private function isHot(Subject $subject)
    {
        return in_array($subject->getUuid(), $this->manager->getHotSubjects($subject->getForum()));
    }
}
