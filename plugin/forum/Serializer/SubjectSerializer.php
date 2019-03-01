<?php

namespace Claroline\ForumBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @DI\Service("claroline.serializer.forum_subject")
 * @DI\Tag("claroline.serializer")
 */
class SubjectSerializer
{
    use SerializerTrait;

    private $serializerProvider;
    private $container;
    private $fileUt;
    private $eventDispatcher;
    private $om;

    private $messageRepo;

    public function getClass()
    {
        return Subject::class;
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

    /**
     * @DI\InjectParams({
     *     "provider"        = @DI\Inject("claroline.api.serializer"),
     *     "container"       = @DI\Inject("service_container"),
     *     "fileUt"          = @DI\Inject("claroline.utilities.file"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param SerializerProvider       $provider
     * @param ContainerInterface       $container
     * @param FileUtilities            $fileUt
     * @param EventDispatcherInterface $eventDispatcher
     * @param ObjectManager            $om
     */
    public function __construct(
        SerializerProvider $provider,
        ContainerInterface $container,
        FileUtilities $fileUt,
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $om
    ) {
        $this->serializerProvider = $provider;
        $this->container = $container;
        $this->fileUt = $fileUt;
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;

        $this->messageRepo = $om->getRepository(Message::class);
    }

    /**
     * Serializes a Subject entity.
     *
     * @param Subject $subject
     * @param array   $options
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
          'restrictions' => $this->serializeRestrictions($subject, $options),
          'poster' => $subject->getPoster() ? $this->container->get('claroline.serializer.public_file')->serialize($subject->getPoster()) : null,
        ];
    }

    public function serializeMeta(Subject $subject, array $options = [])
    {
        $finder = $this->container->get('claroline.api.finder');

        return [
            'views' => $subject->getViewCount(),
            'messages' => $finder->fetch('Claroline\ForumBundle\Entity\Message', ['subject' => $subject->getUuid(), 'parent' => null], null, 0, 0, true),
            /*
            'lastMessages' => array_map(function ($message) {
                return $this->serializerProvider->serialize($message);
            }, $finder->fetch('Claroline\ForumBundle\Entity\Message', ['subject' => $subject->getUuid(), 'parent' => null], ['sortBy' => 'dateCreation', 'direction' => 0], 0, 1)),*/
            'creator' => !empty($subject->getCreator()) ? $this->serializerProvider->serialize($subject->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
            'created' => $subject->getCreationDate()->format('Y-m-d\TH:i:s'),
            'updated' => $subject->getModificationDate()->format('Y-m-d\TH:i:s'),
            'sticky' => $subject->isSticked(),
            'closed' => $subject->isClosed(),
            'flagged' => $subject->isFlagged(),
            'hot' => $this->isHot($subject),
        ];
    }

    public function serializeRestrictions(Subject $subject, array $options = [])
    {
        return [
          'sticky' => true,
          'edit' => true,
          'delete' => false,
        ];
    }

    /**
     * Deserializes data into a Subject entity.
     *
     * @param array   $data
     * @param Subject $subject
     * @param array   $options
     *
     * @return Forum
     */
    public function deserialize($data, Subject $subject, array $options = [])
    {
        $first = $this->messageRepo->findOneBy([
          'subject' => $subject,
          'first' => true,
        ]);

        $this->sipe('id', 'setUuid', $data, $subject);
        $this->sipe('title', 'setTitle', $data, $subject);
        $this->sipe('meta.sticky', 'setSticked', $data, $subject);
        $this->sipe('meta.closed', 'setClosed', $data, $subject);
        $this->sipe('meta.flagged', 'setFlagged', $data, $subject);
        $this->sipe('meta.moderation', 'setModerated', $data, $subject);

        if (isset($data['content'])) {
            if (!$first) {
                $messageData = ['content' => $data['content']];

                if (isset($data['meta']) && isset($data['meta']['creator'])) {
                    $messageData['meta']['creator'] = $data['meta']['creator'];
                }

                $first = new Message();
                $first->setIsFirst(true);
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
                $creator = $this->serializerProvider->deserialize(
                    'Claroline\CoreBundle\Entity\User',
                    $data['meta']['creator']
                );

                if ($creator) {
                    $subject->setCreator($creator);
                    if ($first) {
                        $first->setCreator($creator);
                    }
                }
            }
        }

        if (!empty($data['forum'])) {
            $forum = $this->serializerProvider->deserialize(
                'Claroline\ForumBundle\Entity\Forum',
                $data['forum']
            );

            if ($forum) {
                $subject->setForum($forum);
            }
        }

        if (isset($data['poster'])) {
            $poster = $this->serializerProvider->deserialize(
                'Claroline\CoreBundle\Entity\File\PublicFile',
                $data['poster']
            );
            $subject->setPoster($poster);

            $this->fileUt->createFileUse(
              $poster,
              'Claroline\CoreBundle\Entity\Workspace',
              $subject->getUuid()
          );
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
        $this->eventDispatcher->dispatch('claroline_retrieve_used_tags_by_class_and_ids', $event);

        return $event->getResponse();
    }

    /**
     * Deserializes Item tags.
     *
     * @param Item  $question
     * @param array $tags
     * @param array $options
     */
    private function deserializeTags(Subject $subject, array $tags = [], array $options = [])
    {
        //  if ($this->hasOption(Transfer::PERSIST_TAG, $options)) {
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

        $this->eventDispatcher->dispatch('claroline_tag_multiple_data', $event);
        //}
    }

    private function isHot(Subject $subject)
    {
        $manager = $this->container->get('claroline.manager.forum_manager');

        return in_array($subject->getUuid(), $manager->getHotSubjects($subject->getForum()));
    }
}
