<?php

namespace Icap\LessonBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Repository\ChapterRepository;

class ChapterSerializer
{
    use SerializerTrait;
    public const INCLUDE_INTERNAL_NOTES = 'include_internal_notes';

    private ChapterRepository $chapterRepository;

    public function __construct(
        private readonly ObjectManager $om
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
        $previousChapter = $this->chapterRepository->getPreviousChapter($chapter);
        $nextChapter = $this->chapterRepository->getNextChapter($chapter);

        $serialized = [
            'id' => $chapter->getUuid(),
            'slug' => $chapter->getSlug(),
            'title' => $chapter->getTitle(),
            'poster' => $chapter->getPoster(),
            'text' => $chapter->getText(),
            'customNumbering' => $chapter->getCustomNumbering(),
            'parentSlug' => $chapter->getParent() ? $chapter->getParent()->getSlug() : null,
            'previousSlug' => $previousChapter ? $previousChapter->getSlug() : null,
            'nextSlug' => $nextChapter ? $nextChapter->getSlug() : null,
        ];

        if (in_array(static::INCLUDE_INTERNAL_NOTES, $options)) {
            $serialized['internalNote'] = $chapter->getInternalNote();
        }

        return $serialized;
    }

    /**
     * Serializes a chapter tree, returned from Gedmo tree extension.
     *
     * @return array
     */
    public function serializeChapterTree($tree): array
    {
        return $this->serializeChapterTreeNode($tree);
    }

    public function deserialize(array $data, Chapter $chapter = null): Chapter
    {
        if (empty($chapter)) {
            $chapter = new Chapter();
        }

        $this->sipe('title', 'setTitle', $data, $chapter);
        $this->sipe('text', 'setText', $data, $chapter);
        $this->sipe('customNumbering', 'setCustomNumbering', $data, $chapter);
        $this->sipe('poster', 'setPoster', $data, $chapter);
        $this->sipe('internalNote', 'setInternalNote', $data, $chapter);

        return $chapter;
    }

    private function serializeChapterTreeNode(array $node): array
    {
        $children = [];

        if (!empty($node['__children'])) {
            foreach ($node['__children'] as $child) {
                $children[] = $this->serializeChapterTreeNode($child);
            }
        }

        return [
            'id' => $node['uuid'],
            'title' => $node['title'],
            'slug' => $node['slug'],
            'text' => $node['text'],
            'poster' => $node['poster'],
            'children' => $children,
        ];
    }
}
