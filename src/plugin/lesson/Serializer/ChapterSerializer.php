<?php

namespace Icap\LessonBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Repository\ChapterRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChapterSerializer
{
    use SerializerTrait;

    public const INCLUDE_INTERNAL_NOTES = 'include_internal_notes';

    private ChapterRepository $chapterRepository;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PlaceholderManager $placeholderManager,
        private readonly UserSerializer $userSerializer,
        private readonly ResourceNodeSerializer $resourceNodeSerializer,
    ) {
        $this->chapterRepository = $om->getRepository(Chapter::class);
    }

    public function getClass(): string
    {
        return Chapter::class;
    }

    public function getName(): string
    {
        return 'lesson_chapter';
    }

    public function getSchema(): string
    {
        return '#/plugin/lesson/chapter.json';
    }

    /**
     * Serializes a Chapter entity for the JSON api.
     *
     * @param Chapter $chapter - the Chapter resource to serialize
     * @param array   $options - a list of serialization options
     *
     * @return array - the serialized representation of the Chapter resource
     */
    public function serialize(Chapter $chapter, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $chapter->getUuid(),
                'slug' => $chapter->getSlug(),
                'title' => $chapter->getTitle(),
            ];
        }

        $previousChapter = $this->chapterRepository->getPreviousChapter($chapter);
        $nextChapter = $this->chapterRepository->getNextChapter($chapter);

        $serialized = [
            'id' => $chapter->getUuid(),
            'slug' => $chapter->getSlug(),
            'title' => $chapter->getTitle(),
            'poster' => $chapter->getPoster(),
            'contentRaw' => $chapter->getText(),
            'content' => $this->placeholderManager->replacePlaceholders($chapter->getText() ?? ''),
            'level' => $chapter->getLevel(),
            'meta' => [
                'published' => $chapter->isPublished(),
                'createdAt' => DateNormalizer::normalize($chapter->getCreatedAt()),
                'updatedAt' => DateNormalizer::normalize($chapter->getUpdatedAt()),
                'creator' => $chapter->getCreator() ? $this->userSerializer->serialize($chapter->getCreator(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            ],
            'tags' => $this->serializeTags($chapter),
            'parentSlug' => $chapter->getParent()?->getSlug(),
            'previousSlug' => $previousChapter?->getSlug(),
            'nextSlug' => $nextChapter?->getSlug(),
        ];

        if (in_array(SerializerInterface::SERIALIZE_LIST, $options)) {
            // this is required to create the correct link in DataSource
            $serialized['resourceNode'] = $this->resourceNodeSerializer->serialize($chapter->getLesson()->getResourceNode(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        if (in_array(SerializerInterface::SERIALIZE_TRANSFER, $options) || in_array(static::INCLUDE_INTERNAL_NOTES, $options)) {
            $serialized['internalNote'] = $chapter->getInternalNote();
        }

        return $serialized;
    }

    public function deserialize(array $data, Chapter $chapter, ?array $options = []): Chapter
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $chapter);
            $this->sipe('slug', 'setSlug', $data, $chapter);
        } else {
            $chapter->refreshUuid();
        }

        $this->sipe('title', 'setTitle', $data, $chapter);
        $this->sipe('contentRaw', 'setText', $data, $chapter);
        $this->sipe('poster', 'setPoster', $data, $chapter);
        $this->sipe('internalNote', 'setInternalNote', $data, $chapter);

        if (isset($data['meta'])) {
            $this->sipe('meta.published', 'setPublished', $data, $chapter);

            if (array_key_exists('created', $data['meta'])) {
                $chapter->setCreatedAt(DateNormalizer::denormalize($data['meta']['created']));
            }
            if (array_key_exists('updated', $data['meta'])) {
                $chapter->setUpdatedAt(DateNormalizer::denormalize($data['meta']['updated']));
            }

            if (array_key_exists('creator', $data['meta'])) {
                $creator = null;
                if (!empty($data['meta']['creator'])) {
                    /** @var User $creator */
                    $creator = $this->om->getObject($data['meta']['creator'], User::class);
                }

                $chapter->setCreator($creator);
            }
        }

        if (isset($data['tags'])) {
            $this->deserializeTags($chapter, $data['tags'], $options);
        }

        return $chapter;
    }

    private function serializeTags(Chapter $chapter): array
    {
        $event = new GenericDataEvent([
            'class' => Chapter::class,
            'ids' => [$chapter->getUuid()],
        ]);

        $this->eventDispatcher->dispatch($event, 'claroline_retrieve_used_tags_by_class_and_ids');

        return $event->getResponse() ?? [];
    }

    private function deserializeTags(Chapter $chapter, array $tags = [], array $options = []): void
    {
        if (in_array(Options::PERSIST_TAG, $options)) {
            $event = new GenericDataEvent([
                'tags' => $tags,
                'data' => [
                    [
                        'class' => Chapter::class,
                        'id' => $chapter->getUuid(),
                        'name' => $chapter->getTitle(),
                    ],
                ],
                'replace' => true,
            ]);

            $this->eventDispatcher->dispatch($event, 'claroline_tag_multiple_data');
        }
    }
}
